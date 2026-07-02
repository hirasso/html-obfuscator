<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Support;

use Dom\DocumentFragment;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\XPath;
use InvalidArgumentException;

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
     * Parse a HTML fragment
     */
    public static function parseHtmlFragment(string $html, ?HTMLDocument $document = null): DocumentFragment
    {
        if (str_contains($html, '</body>')) {
            throw new InvalidArgumentException('Can only parse HTML fragments, not full documents'); // @codeCoverageIgnore
        }

        $document ??= self::createDocument($html);

        /** parse using ->innerHTML */
        $div = $document->createElement('div');
        $div->innerHTML = $html;

        /** add to a document fragment */
        $fragment = $document->createDocumentFragment();
        $fragment->append(...$div->childNodes);

        return $fragment;
    }

    /** @return list<\Dom\Text> */
    public static function getTextNodes(HTMLDocument $doc, ?Element $context = null): array
    {
        $query = $context
            ? './/text()[normalize-space() != ""]'
            : '//text()[normalize-space() != ""]';

        /** @var list<\Dom\Text> */
        return array_values(array_filter(
            [...new XPath($doc)->query($query, $context)],
            fn ($node) => !$node->parentElement?->closest(
                'head, script, style, svg, noscript, title, textarea, select, iframe, canvas, pre, code'
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
