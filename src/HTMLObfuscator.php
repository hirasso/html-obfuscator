<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Text;
use Hirasso\HTMLObfuscator\Enum\RevealStrategy;
use Hirasso\HTMLObfuscator\ScriptSettings\ScriptSettings;
use Hirasso\HTMLObfuscator\Support\Support;
use InvalidArgumentException;
use RuntimeException;

/**
 * Obfuscate emails and phone numbers to protect them from spam bots
 *
 * @see https://spencermortensen.com/articles/email-obfuscation/
 */
final class HTMLObfuscator
{
    private const string EMAIL_REGEX = "[^\s@]+@[^\s@]+\.[^\s@]{2,}";
    private const string PHONE_NUMBER_REGEX = "[\+\d][\d \-\(\)\.]{6,20}(?<!\s)";

    public const string DEFAULT_TAG_NAME = 'x-obfuscated';

    private string $passphrase = 'html-obfuscator';
    private string $tagName = self::DEFAULT_TAG_NAME;
    private bool $debug = false;

    private bool $randomizeKey = true;

    private bool $emails = true;
    private bool $phoneNumbers = true;

    private bool $injectFrontendScript = true;

    private ScriptSettings $scriptSettings;

    /** @internal */
    public static bool $hasInjectedFrontendScript = false;

    private function __construct(
        private HTMLDocument $document,
        private bool $isPartial
    ) {
        $this->scriptSettings = new ScriptSettings();
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
     * Should emails be obfuscated?
     */
    public function emails(bool $enabled = true): self
    {
        $this->emails = $enabled;
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
     * Should the passphrase be randomized each time?
     */
    public function randomizeKey(bool $enabled = true): self
    {
        $this->randomizeKey = $enabled;
        return $this;
    }

    /**
     * Set a custom passphrase for improved security
     */
    public function withPassphrase(string $passphrase): self
    {
        $this->passphrase = $passphrase;
        return $this;
    }

    /**
     * Customize the tag name of the obfuscated element
     * Must contain at least one "-", as defined by the Spec
     */
    public function withTagName(string $tagName): self
    {
        if (!str_contains($tagName, '-')) {
            throw new InvalidArgumentException('The tag name needs to contain at least one dash');
        }

        $this->tagName = trim($tagName);
        $this->scriptSettings->tagName = $this->tagName;
        return $this;
    }

    /**
     * Set the reveal strategy
     */
    public function withRevealStrategy(RevealStrategy $revealStrategy): self
    {
        $this->scriptSettings->revealStrategy = $revealStrategy;
        return $this;
    }

    /**
     * Should placeholders be rendered for obfuscated elements?
     */
    public function renderPlaceholders(bool $enabled = true): self
    {
        $this->scriptSettings->renderPlaceholders = $enabled;
        return $this;
    }

    /**
     * Should the deobfuscation script be injected or not?
     */
    public function injectFrontendScript(bool $enabled = true): self
    {
        $this->injectFrontendScript = $enabled;
        return $this;
    }

    /**
     * Activate debug mode. Currently, this has only one effect:
     *
     *  - The deobfuscation JavaScript will be injected un-minified
     */
    public function debug(bool $enabled = true): self
    {
        $this->debug = $enabled;
        $this->scriptSettings->debug = $this->debug;
        return $this;
    }

    /**
     * Apply the obfuscation to the document
     */
    public function apply(): self
    {
        $this->maybeInjectFrontendScript($this->document);
        $this->obfuscateLinks($this->document);

        if ($this->emails) {
            foreach (Support::getTextNodes($this->document) as $node) {
                $this->obfuscateTextNode($node, self::EMAIL_REGEX);
            }
        }
        if ($this->phoneNumbers) {
            foreach (Support::getTextNodes($this->document) as $node) {
                $this->obfuscateTextNode($node, self::PHONE_NUMBER_REGEX);
            }
        }

        return $this;
    }

    /**
     * Get the document we are working on
     */
    public function getDocument(): HTMLDocument
    {
        return $this->document; // @codeCoverageIgnore
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
            return Support::outerHTML($this->document->documentElement);
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
     * Get the key for encoding and decoding
     */
    private function getKey(): string
    {
        $passphrase = $this->randomizeKey
            ? Support::shuffleString($this->passphrase)
            : $this->passphrase;

        return md5($passphrase);
    }

    /**
     * Obfuscate links
     */
    private function obfuscateLinks(HTMLDocument $document): void
    {
        if ($this->emails) {
            foreach ($document->querySelectorAll('a[href*="mailto:"]') as $el) {
                $email = substr($el->getAttribute('href') ?? '', strlen('mailto:'));
                if (!preg_match('/^' . self::EMAIL_REGEX . '$/', $email)) {
                    continue;
                }
                $el->replaceWith($this->obfuscateElement($el));
            }
        }
        if ($this->phoneNumbers) {
            foreach ($document->querySelectorAll('a[href*="tel:"]') as $el) {
                $tel = substr($el->getAttribute('href') ?? '', strlen('tel:'));
                if (!preg_match('/^' . self::PHONE_NUMBER_REGEX . '$/', $tel)) {
                    continue;
                }
                $el->replaceWith($this->obfuscateElement($el));
            }
        }
    }

    /**
     * Obfuscate an element
     */
    private function obfuscateElement(Element $el): Element
    {
        if (!($el->ownerDocument instanceof HTMLDocument)) {
            throw new RuntimeException('Only elements within a HTML document can be obfuscated'); // @codeCoverageIgnore
        }

        return $this->createObfuscatedElement(
            value: Support::outerHTML($el),
            charCount: mb_strlen($el->textContent ?? ''),
            document: $el->ownerDocument
        );
    }

    /**
     * Create an obfuscated element
     */
    private function createObfuscatedElement(
        string $value,
        int $charCount,
        HTMLDocument $document
    ): Element {
        $key = $this->getKey();

        $el = $document->createElement($this->tagName);
        $el->setAttribute('value', $this->encode($value, $key));
        $el->setAttribute('key', $key);
        $el->setAttribute('tabindex', '0');
        $el->setAttribute('char-count', (string) $charCount);

        return $el;
    }

    /**
     * Obfuscate a text node
     */
    private function obfuscateTextNode(Text $node, string $regex): void
    {
        if (!($node->ownerDocument instanceof HTMLDocument)) {
            throw new RuntimeException('Only text nodes within HTML documents can be obfuscated'); // @codeCoverageIgnore
        }

        /** obfuscate */
        $obfuscated = preg_replace_callback(
            "/{$regex}/",
            function ($matches) use ($node) {
                $value = $matches[0];
                $el = $this->createObfuscatedElement($value, mb_strlen($value), $node->ownerDocument);

                return Support::outerHTML($el);
            },
            $node->data
        ) ?? $node->data;

        /** nothing changed, ignore */
        if ($obfuscated === $node->data) {
            return;
        }

        /** No tags? ignore */
        if (!str_contains($obfuscated, '<')) {
            return; // @codeCoverageIgnore
        }

        $fragment = Support::parseHtmlFragment($obfuscated, $node->ownerDocument);

        $node->replaceWith($fragment);
    }

    /**
     * Encode a string, using a passphrase key
     */
    private function encode(string $data, string $key): string
    {
        $out = '';
        for ($i = 0; $i < mb_strlen($data); $i++) {
            $out .= mb_substr($data, $i, 1) ^ mb_substr($key, $i % mb_strlen($key), 1);
        }
        return base64_encode($out);
    }

    /**
     * Inject the script that de-obfuscates obfuscated emails in the frontend.
     * This intentionally runs only ONCE per PHP process, since we only need it once
     */
    private function maybeInjectFrontendScript(HTMLDocument $document): void
    {
        if (self::$hasInjectedFrontendScript || !$this->injectFrontendScript) {
            return;
        }
        self::$hasInjectedFrontendScript = true;

        $script = $document->createElement('script');
        $script->setAttribute('data-settings', \json_encode(
            value: $this->scriptSettings,
            flags: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
        $rootPath = dirname(__DIR__);

        $filePath = $this->debug
            ? '/resources/dist/html-obfuscator.iife.min.js'
            : '/resources/dist/html-obfuscator.iife.min.js';

        $js = file_get_contents($rootPath . $filePath) ?: '';
        $js = str_replace('x-obfuscated', $this->tagName, $js);

        $script->textContent = $js;
        $document->body?->append($script);
    }
}
