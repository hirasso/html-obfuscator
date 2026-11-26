<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Support;

use Dom\DocumentFragment;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\XPath;
use InvalidArgumentException;

final class DomHelper
{
    /**
     * Parse a HTML fragment
     */
    public static function parseHtmlFragment(string $html, ?HTMLDocument $document = null): DocumentFragment
    {
        if (str_contains($html, '</body>')) {
            throw new InvalidArgumentException('Can only parse HTML fragments, not full documents'); // @codeCoverageIgnore
        }

        $document ??= HTMLDocument::createFromString($html, LIBXML_NOERROR);

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
     * Get the strings before and after the body's content of a HTML string.
     * Falls back to `</head>`, `<html>` or the doctype if there is no `<body>`
     *
     * @return array{0: string, 1: string}
     */
    public static function getBeforeAndAfterBody(string $source): array
    {
        /** blank out comments and raw text elements, so tags inside them aren't matched */
        $masked = preg_replace_callback(
            '/<!--.*?(?:-->|$)|<(script|style|title|textarea)\b.*?(?:<\/\1\s*>|$)/is',
            fn ($match) => str_repeat(' ', strlen($match[0])),
            $source
        ) ?? $source;

        $start = self::getOffsetAfter($masked, '/<body(?=[\s\/>])[^>]*>/i')
            ?? self::getOffsetAfter($masked, '/<\/head\s*>/i')
            ?? self::getOffsetAfter($masked, '/<html(?=[\s\/>])[^>]*>/i')
            ?? self::getOffsetAfter($masked, '/<!doctype[^>]*>/i')
            ?? 0;

        $end = self::getOffset($masked, '/<\/body\s*>/i', $start)
            ?? self::getOffset($masked, '/<\/html\s*>/i', $start)
            ?? strlen($source);

        return [substr($source, 0, $start), substr($source, $end)];
    }

    /**
     * Get the offset of the first match of $pattern, starting at $offset
     */
    private static function getOffset(string $subject, string $pattern, int $offset = 0): ?int
    {
        return preg_match($pattern, $subject, $match, PREG_OFFSET_CAPTURE, $offset) === 1
            ? $match[0][1]
            : null;
    }

    /**
     * Get the offset right after the first match of $pattern
     */
    private static function getOffsetAfter(string $subject, string $pattern): ?int
    {
        return preg_match($pattern, $subject, $match, PREG_OFFSET_CAPTURE) === 1
            ? $match[0][1] + strlen($match[0][0])
            : null;
    }
}
