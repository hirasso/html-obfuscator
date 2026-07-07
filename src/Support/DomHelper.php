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
}
