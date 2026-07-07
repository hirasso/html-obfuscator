<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Text;
use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;
use Hirasso\HTMLObfuscator\Obfuscation\Obfuscator;
use Hirasso\HTMLObfuscator\Support\Support;

/**
 * Obfuscate emails and phone numbers to protect them from spam bots
 *
 * @see https://spencermortensen.com/articles/email-obfuscation/
 */
final class HTMLObfuscator
{
    public const string DEFAULT_TAG_NAME = 'ob-fus-ca-ted';
    public const string OBFUSCATE_TEXT_ATTR = 'obfuscate-text';

    private const string PATTERN_EMAIL = '/(?:mailto:)?[^\s@]+@[^\s@]+\.[^\s@]{2,}/';
    private const string PATTERN_PHONE = '/(?:tel:)?[\+\d][\d \-\(\)\.]{6,20}(?<!\s)/';

    private ?string $ariaLabel = 'Interact with the page to reveal';
    private ?string $noscriptText = 'Please activate JavaScript';

    private bool $debug = false;

    private bool $emails = true;
    private bool $phoneNumbers = true;

    /** @var list<string> */
    private array $customPatterns = [];

    private bool $injectClientScript = true;

    private Obfuscator $obfuscation;

    private function __construct(
        private HTMLDocument $document,
        private bool $isPartial = false,
    ) {
        $this->obfuscation = new Obfuscator();
    }

    /**
     * Create a new Obfuscator instance from a HTMLDocument (by reference)
     */
    public static function createFromDocument(HTMLDocument $document): self
    {
        return new self($document, isPartial: false);
    }

    /**
     * Create a new Obfuscator instance from a HTML string
     */
    public static function createFromString(string $source): self
    {
        $isPartial = !str_contains($source, '</body>');

        return new self(Support::createDocument($source), isPartial: $isPartial);
    }

    /**
     * Create an empty instance
     */
    public static function createEmpty(): self
    {
        return new self(HTMLDocument::createEmpty());
    }

    /**
     * Create an instance for rendering the client script only
     */
    public static function createForClientScript(): self
    {
        ObfuscatorConfig::$hasInjectedClientScript = false;
        return self::createEmpty();
    }

    /**
     * Should emails be obfuscated?
     */
    public function emails(bool $enabled = true): self
    {
        $this->emails = $enabled;
        return $this;
    }

    /**
     * Set a specific strategy
     * @param class-string<ObfuscationStrategy> $strategy
     */
    public function setStrategy(string $strategy): self
    {
        $this->obfuscation->setStrategy($strategy);

        return $this;
    }

    /**
     * Add a custom PCRE regex pattern (with delimiters) to obfuscate matching text nodes
     */
    public function addRegex(string $pattern): self
    {
        set_error_handler(fn () => true);
        $valid = preg_match($pattern, '') !== false;
        restore_error_handler();

        if (!$valid) {
            throw new \InvalidArgumentException("Invalid regex pattern: {$pattern}");
        }
        $this->customPatterns[] = $pattern;
        return $this;
    }

    /**
     * Should phone numbers be obfuscated?
     */
    public function phoneNumbers(bool $enabled = true): self
    {
        $this->phoneNumbers = $enabled;
        return $this;
    }

    /**
     * Customize the tag name of the obfuscated element
     * Must contain at least one "-", as defined by the Spec
     */
    public function withTagName(string $tagName): self
    {
        ObfuscatorConfig::setTagName($tagName);
        return $this;
    }

    /**
     * Customize or disable the aria-label on obfuscated elements
     */
    public function withAriaLabel(?string $text = null): self
    {
        $this->ariaLabel = $text;
        return $this;
    }

    /**
     * Customize or disable the <noscript> fallback inside obfuscated elements
     */
    public function withNoscriptText(?string $text = null): self
    {
        $this->noscriptText = $text;
        return $this;
    }

    /**
     * Should the deobfuscation script be injected or not?
     */
    public function injectClientScript(bool $enabled = true): self
    {
        $this->injectClientScript = $enabled;
        return $this;
    }

    /**
     * Activate debug mode. Currently, this has only one effect:
     *
     *  - The client script will be injected un-minified and with a logger
     */
    public function debug(bool $enabled = true): self
    {
        $this->debug = $enabled;
        return $this;
    }

    /**
     * Apply the obfuscation to the document
     */
    public function apply(): self
    {
        $this->maybeInjectClientScript();

        $this->processObfuscateTextElements();
        $this->obfuscateLinks();
        $this->obfuscateTextNodes();

        return $this;
    }

    /**
     * Apply changes and get the document
     */
    public function saveDocument(): HTMLDocument
    {
        $this->apply();
        return $this->document;
    }

    /**
     * Apply obfuscation and return the result as a string
     */
    public function saveHTML(): string
    {
        $this->apply();

        if ($this->isPartial) {
            return $this->document->body->innerHTML ?? '';
        }

        if ($this->document->documentElement) {
            return $this->document->saveHtml();
        }

        return ''; // @codeCoverageIgnore
    }

    /**
     * Stringable
     */
    public function __toString(): string
    {
        return $this->saveHTML();
    }

    /**
     * Obfuscate all text nodes inside [obfuscate-text] elements wholesale
     */
    private function processObfuscateTextElements(): void
    {
        foreach ($this->document->querySelectorAll('[' . self::OBFUSCATE_TEXT_ATTR . ']') as $el) {
            $el->removeAttribute(self::OBFUSCATE_TEXT_ATTR);
            foreach (Support::getTextNodes($this->document, $el) as $node) {
                $el = $this->createObfuscatedTextElement($node->data);
                $node->replaceWith($el);
            }
        }
    }

    /**
     * Obfuscate links
     */
    private function obfuscateLinks(): void
    {
        foreach ($this->document->querySelectorAll('a[href]') as $el) {
            $this->obfuscateAttribute('href', $el);
        }
    }

    /**
     * Obfuscate an element's attribute
     */
    private function obfuscateAttribute(
        string $attibuteName,
        Element $el,
    ): void {
        $value = $el->getAttribute($attibuteName) ?? '';

        if (trim($value) === '') {
            return;
        }

        /** apply only the first regex that matches */
        $pattern = \array_find(
            $this->builtinPatterns(),
            fn ($p) => !!preg_match($p, $value)
        );

        if (!$pattern) {
            return;
        }


        $obfuscated = $this->createObfuscatedAttributeElement(
            $value,
            $attibuteName
        );

        $el->prepend($obfuscated);

        /** Keep only the scheme from the original attribute value */
        $colon = strpos($value, ':');
        $el->setAttribute($attibuteName, $colon !== false ? substr($value, 0, $colon + 1) : '');
    }

    /**
     * Create an obfuscated element for a text node
     */
    private function createObfuscatedTextElement(string $value): Element
    {
        $el = $this->document->createElement(ObfuscatorConfig::getTagName());
        $el->setAttribute('value', $this->obfuscation->getAttribute($value));

        if ($this->debug) {
            $el->setAttribute('identifier', $this->obfuscation->getIdentifier());
        }

        if ($this->ariaLabel) {
            $el->setAttribute('aria-label', $this->ariaLabel);
        }

        if ($this->noscriptText) {
            $noscript = $this->document->createElement('noscript');
            $noscript->textContent = $this->noscriptText;
            $el->append($noscript);
        }

        return $el;
    }

    /**
     * Create an obfuscated element targeting a parent's attribute
     */
    private function createObfuscatedAttributeElement(string $value, string $attribute): Element
    {
        $el = $this->document->createElement(ObfuscatorConfig::getTagName());
        $el->setAttribute('attr', $attribute);

        if ($this->debug) {
            $el->setAttribute('identifier', $this->obfuscation->getIdentifier());
        }

        $el->setAttribute('value', $this->obfuscation->getAttribute($value));
        $el->setAttribute('style', 'display:none');

        return $el;
    }

    /** @return list<string> */
    private function builtinPatterns(): array
    {
        $patterns = [];
        if ($this->emails) {
            $patterns[] = self::PATTERN_EMAIL;
        }
        if ($this->phoneNumbers) {
            $patterns[] = self::PATTERN_PHONE;
        }
        return $patterns;
    }

    /**
     * Obfuscate all text nodes
     */
    private function obfuscateTextNodes(): void
    {
        $patterns = [...$this->builtinPatterns(), ...$this->customPatterns];

        foreach ($patterns as $pattern) {
            foreach (Support::getTextNodes($this->document) as $node) {
                $this->obfuscateTextNode($node, $pattern);
            }
        }
    }

    /**
     * Obfuscate a text node
     */
    private function obfuscateTextNode(Text $node, string $pattern): void
    {
        $value = $node->data;

        /** obfuscate */
        $value = preg_replace_callback(
            $pattern,
            function ($matches) {
                $el = $this->createObfuscatedTextElement($matches[0]);

                return Support::outerHTML($el);
            },
            $value
        ) ?? $value;


        /** nothing changed, ignore */
        if ($value === $node->data) {
            return;
        }

        /** No tags? ignore */
        if (!str_contains($value, '<')) {
            return; // @codeCoverageIgnore
        }

        $fragment = Support::parseHtmlFragment($value, $this->document);

        $node->replaceWith($fragment);

    }

    /**
     * Inject the script that de-obfuscates obfuscated emails in the frontend.
     * This intentionally runs only ONCE per PHP process, since we only need it once
     */
    private function maybeInjectClientScript(): void
    {
        if (ObfuscatorConfig::$hasInjectedClientScript || !$this->injectClientScript) {
            return;
        }

        ObfuscatorConfig::$hasInjectedClientScript = true;

        /** the script tag */
        $js = $this->getResource($this->debug ? 'index.js' : 'index.min.js');

        $script = $this->document->createElement('script');
        $script->textContent = $js;

        $script->setAttribute('data-tagname', ObfuscatorConfig::getTagName());

        ($this->document->body ?? $this->document)->append($script);
    }

    /**
     * Get a resource, replace the default tag name with the actual tag name
     */
    private function getResource(string $path): string
    {
        $root = dirname(__DIR__);
        $path = ltrim($path, "/");
        $resource = file_get_contents("{$root}/resources/dist/$path") ?: '';
        return str_replace(self::DEFAULT_TAG_NAME, ObfuscatorConfig::getTagName(), $resource);
    }
}
