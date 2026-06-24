<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Text;
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

    private string $passphrase = 'html-obfuscator';
    private string $customElementName = 'x-obfuscated';
    private bool $randomizeKey = true;

    private bool $emails = true;
    private bool $phoneNumbers = true;

    public static bool $jsInjected = false;
    private bool $injectJS = true;

    private function __construct(
        private HTMLDocument $document,
        private bool $isPartial
    ) {
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
     */
    public function withCustomElementName(string $name): self
    {
        if (!str_contains($name, '-')) {
            throw new InvalidArgumentException('The custom element name needs to contain at least one dash');
        }

        $this->customElementName = trim($name);
        return $this;
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
     * Should the deobfuscation script be injected or not?
     */
    public function injectDeobfuscationScript(bool $enabled = true): self
    {
        $this->injectJS = $enabled;
        return $this;
    }

    /**
     * Apply the obfuscation to the document
     */
    public function apply(): self
    {
        $this->maybeInjectJS($this->document);
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

    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Obfuscate links
     */
    private function obfuscateLinks(HTMLDocument $document): void
    {
        if ($this->emails) {
            foreach ($document->querySelectorAll('a[href*="mailto:"]') as $link) {
                $email = substr($link->getAttribute('href') ?? '', strlen('mailto:'));
                if (!preg_match('/^' . self::EMAIL_REGEX . '$/', $email)) {
                    continue;
                }
                $link->replaceWith($this->obfuscateElement($link));
            }
        }
        if ($this->phoneNumbers) {
            foreach ($document->querySelectorAll('a[href*="tel:"]') as $link) {
                $tel = substr($link->getAttribute('href') ?? '', strlen('tel:'));
                if (!preg_match('/^' . self::PHONE_NUMBER_REGEX . '$/', $tel)) {
                    continue;
                }
                $link->replaceWith($this->obfuscateElement($link));
            }
        }
    }

    /**
     * Obfuscate an element
     */
    private function obfuscateElement(Element $el): Element
    {
        if (!$el->ownerDocument) {
            throw new RuntimeException('No owner document found'); // @codeCoverageIgnore
        }
        $key = $this->getKey();

        $obfuscated = $el->ownerDocument->createElement($this->customElementName);
        $obfuscated->setAttribute('value', $this->encode(Support::outerHTML($el), $key));
        $obfuscated->setAttribute('key', $key);

        return $obfuscated;
    }

    /**
     * Obfuscate a text node
     */
    private function obfuscateTextNode(Text $node, string $regex): void
    {
        $obfuscated = preg_replace_callback(
            "/{$regex}/",
            fn ($matches) => $this->obfuscateText($matches[0]),
            $node->data
        ) ?? $node->data;

        if ($obfuscated === $node->data) {
            return;
        }

        $node->data = $obfuscated;
        Support::hydrateTextNode($node);
    }

    /**
     * Obfuscate a string
     */
    private function obfuscateText(string $value): string
    {
        $key = $this->getKey();
        $encodedValue = $this->encode($value, $key);

        return sprintf(
            <<<HTML
            <$this->customElementName value="%s" key="%s"></$this->customElementName>
            HTML,
            $encodedValue,
            $key
        );
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
    private function maybeInjectJS(HTMLDocument $document): void
    {
        if (self::$jsInjected || !$this->injectJS) {
            return;
        }
        self::$jsInjected = true;

        $script = $document->createElement('script');
        $script->setAttribute('type', 'module');
        $script->textContent = file_get_contents(dirname(__DIR__). '/resources/html-obfuscator.js') ?: '';
        $script->textContent = str_replace("x-obfuscated", $this->customElementName, $script->textContent);
        $document->body?->append($script);
    }
}
