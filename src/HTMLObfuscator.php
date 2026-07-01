<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Text;
use Hirasso\HTMLObfuscator\Enum\Regex;
use Hirasso\HTMLObfuscator\Support\Support;
use InvalidArgumentException;

/**
 * Obfuscate emails and phone numbers to protect them from spam bots
 *
 * @see https://spencermortensen.com/articles/email-obfuscation/
 */
final class HTMLObfuscator
{
    public const string DEFAULT_TAG_NAME = 'x-obfuscated';

    private string $tagName = self::DEFAULT_TAG_NAME;

    private string $key;
    private bool $debug = false;

    private bool $emails = true;
    private bool $phoneNumbers = true;

    private bool $injectFrontendScript = true;

    /** @internal */
    public static bool $hasInjectedFrontendScript = false;

    private function __construct(
        private HTMLDocument $document,
        string $passphrase,
        private bool $isPartial,
    ) {
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
     * Customize the tag name of the obfuscated element
     * Must contain at least one "-", as defined by the Spec
     */
    public function withTagName(string $tagName): self
    {
        if (!str_contains($tagName, '-')) {
            throw new InvalidArgumentException('The tag name needs to contain at least one dash');
        }

        $this->tagName = trim($tagName);
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
        return $this;
    }

    /**
     * Apply the obfuscation to the document
     */
    public function apply(): self
    {
        $this->maybeInjectFrontendScript();

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
        $regex = Support::first(
            Regex::get($this->emails, $this->phoneNumbers),
            fn ($r) => !!preg_match('/' . $r->value . '$/', $value)
        );

        if (!$regex) {
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

        $el = $this->document->createElement($this->tagName);
        $el->setAttribute('value', $value->encoded);

        return $el;
    }

    /**
     * Obfuscate all text nodes
     */
    private function obfuscateTextNodes(): void
    {
        foreach (Regex::get($this->emails, $this->phoneNumbers) as $regex) {
            /** parse again for each regex */
            foreach (Support::getTextNodes($this->document) as $node) {
                $this->obfuscateTextNode($node, $regex);
            }
        }
    }

    /**
     * Obfuscate a text node
     */
    private function obfuscateTextNode(Text $node, Regex $regex): void
    {
        $value = $node->data;

        /** obfuscate */
        $value = preg_replace_callback(
            "/{$regex->value}/",
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
    private function maybeInjectFrontendScript(): void
    {
        if (!$this->document->body) {
            return;
        }

        if (self::$hasInjectedFrontendScript || !$this->injectFrontendScript) {
            return;
        }

        self::$hasInjectedFrontendScript = true;

        /** the script tag */
        $js = $this->getResource($this->debug ? 'index.js' : 'index.min.js');
        $wrapper = $this->document->createElement('html-obfuscator');
        $wrapper->innerHTML = <<<HTML
            <template shadowrootmode="closed">
                <script data-tagname="$this->tagName" data-key="$this->key">
                    $js
                </script>
            </template>
        HTML;
        $script = $this->document->createElement('script');

        $script->textContent = $js;
        $script->setAttribute('data-tagname', $this->tagName);
        $script->setAttribute('data-key', $this->key);
        $this->document->body->append($script);

    }

    /**
     * Get a resource, replace the default tag name with the actual tag name
     */
    private function getResource(string $path): string
    {
        $root = dirname(__DIR__);
        $path = ltrim($path, "/");
        $resource = file_get_contents("{$root}/resources/dist/$path") ?: '';
        return str_replace(self::DEFAULT_TAG_NAME, $this->tagName, $resource);
    }
}
