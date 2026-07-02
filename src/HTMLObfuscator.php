<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Text;
use Hirasso\HTMLObfuscator\Support\Support;

/**
 * Obfuscate emails and phone numbers to protect them from spam bots
 *
 * @see https://spencermortensen.com/articles/email-obfuscation/
 */
final class HTMLObfuscator
{
    public const string DEFAULT_TAG_NAME = 'ob-fus-ca-ted';

    private const string PATTERN_EMAIL = '/(?:mailto:)?[^\s@]+@[^\s@]+\.[^\s@]{2,}/';
    private const string PATTERN_PHONE = '/(?:tel:)?[\+\d][\d \-\(\)\.]{6,20}(?<!\s)/';

    private string $key;

    private bool $debug = false;

    private bool $emails = true;
    private bool $phoneNumbers = true;

    /** @var list<string> */
    private array $customPatterns = [];

    private bool $injectClientScript = true;

    private function __construct(
        private HTMLDocument $document,
        string $passphrase,
        private bool $isPartial = false,
    ) {
        ObfuscatorConfig::setPassphrase($passphrase);
        $this->key = md5($passphrase);
    }

    /**
     * Create a new Obfuscator instance from a HTMLDocument (by reference)
     */
    public static function createFromDocument(HTMLDocument $document, string $passphrase): self
    {
        return new self($document, passphrase: $passphrase, isPartial: false);
    }

    /**
     * Create a new Obfuscator instance from a HTML string
     */
    public static function createFromString(string $source, string $passphrase): self
    {
        $isPartial = !str_contains($source, '</body>');

        return new self(Support::createDocument($source), passphrase: $passphrase, isPartial: $isPartial);
    }

    /**
     * Create an empty instance
     */
    public static function createEmpty(string $passphrase): self
    {
        return new self(HTMLDocument::createEmpty(), $passphrase);
    }

    /**
     * Create an instance for rendering the client script only
     */
    public static function createForClientScript(string $passphrase): self
    {
        ObfuscatorConfig::$hasInjectedClientScript = false;
        return self::createEmpty($passphrase);
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

        $this->obfuscateLinks();
        $this->obfuscateTextNodes();

        return $this;
    }

    /**
     * Get the document we are working on
     */
    public function getDocument(): HTMLDocument
    {
        return $this->document;
    }

    /**
     * Apply obfuscation and return the result as a string
     */
    public function render(): string
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
        return $this->render();
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

        $obfuscatedValue = new ObfuscatedValue($value, $this->key);
        $obfuscated = $this->createObfuscatedElement($obfuscatedValue);
        $obfuscated->setAttribute('attr', $attibuteName);
        $obfuscated->setAttribute('style', 'display:none');

        $el->prepend($obfuscated);
        /** clear the original attribute value */
        $el->setAttribute($attibuteName, '');
    }

    /**
     * Create an obfuscated element
     */
    private function createObfuscatedElement(
        ObfuscatedValue $value,
    ): Element {

        $el = $this->document->createElement(ObfuscatorConfig::getTagName());
        $el->setAttribute('value', $value->encoded);

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
                $obfuscated = new ObfuscatedValue($matches[0], $this->key);
                $el = $this->createObfuscatedElement($obfuscated);
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

        $script->setAttribute('data-key', $this->key);
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
