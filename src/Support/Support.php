<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Support;

use Dom\DocumentFragment;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Dom\XPath;
use RuntimeException;

final class Support
{
    /**
     * Create a document from a HTML string
     */
    public static function createDocument(string $html): HTMLDocument
    {
        return HTMLDocument::createFromString(
            $html,
            LIBXML_NOERROR,
        );
    }

    /**
     * Extract the innerHTML from a document's <body>
     */
    public static function extractBodyHTML(HTMLDocument $document): string
    {
        return $document->body->innerHTML ?? '';
    }

    /**
     * Parse the text in a text node, if it contains HTML
     */
    public static function parseHtml(string $html): DocumentFragment
    {
        $doc = self::createDocument($html);

        $fragment = $doc->createDocumentFragment();
        $fragment->append(...$doc->body->childNodes ?? []);

        // HTML parsers strip leading whitespace from <body>; restore it manually
        if (preg_match('/^(\s+)/', $html, $m)) {
            $fragment->prepend($doc->createTextNode($m[1]));
        }

        return $fragment;
    }

    /**
     * Hydrate HTML tags within a text node
     */
    public static function hydrateTextNode(Text $node): void
    {
        /** No tags? We don't need hydration */
        if (!str_contains($node->data, '<')) {
            return; // @codeCoverageIgnore
        }

        if (!$document = $node->ownerDocument) {
            throw new RuntimeException('Text nodes without ownerDocument can\'t be hydrated'); // @codeCoverageIgnore
        }

        $parsed = self::parseHtml($node->data);

        $node->replaceWith($document->importNode($parsed, deep: true));
    }

    /**
     * Shuffle a string
     */
    public static function shuffleString(string $str): string
    {
        $chars = mb_str_split($str);
        shuffle($chars);
        return implode('', $chars);
    }

    /** @return list<\Dom\Text> */
    public static function getTextNodes(HTMLDocument $doc): array
    {
        /** @var list<\Dom\Text> */
        return array_values(array_filter(
            [...new XPath($doc)->query('//text()[normalize-space() != ""]')],
            fn ($node) => !$node->parentElement?->closest(
                'head, script, style, svg, noscript, title, textarea, select, iframe, canvas'
            )
        ));
    }

    /**
     * Trim lines from a string of text
     */
    public static function trimLines(string $text): string
    {
        return implode("\n", array_map(
            'trim',
            preg_split("/\R/", $text) ?: []
        ));
    }

    /**
     * Trim whitespace from a string of text
     */
    public static function trimWhitespace(string $text): string
    {
        return str_replace("\n", '', self::trimLines($text));
    }

    /**
     * Get the outer HTML of an element (not implemented natively, yet)
     */
    public static function outerHTML(Element $el): string
    {
        $doc = HTMLDocument::createEmpty();
        $doc->appendChild($doc->importNode($el, true));
        return $doc->saveHTML();
    }
}
