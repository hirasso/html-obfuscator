<?php

use Dom\XPath;
use Hirasso\HTMLObfuscator\Support\Support;

test('parseHtml() preserves leading and trailing whitespace', function () {
    $parsed = Support::parseHtml(' <em>please</em> preserve the whitespace ');
    expect(count($parsed->childNodes ?? []))->toBe(3);
    expect($parsed->textContent ?? null)->toBe(" please preserve the whitespace ");
});

test('getTextNodes() ignores empty nodes', function () {
    $doc = Support::createDocument('<p>hello <span>world</span> <span> </span><span> <!-- foo --></span>!</p>');

    $unfiltered = [...new XPath($doc)->query('//text()')];
    expect(count($unfiltered))->toBe(6);

    $filtered = Support::getTextNodes($doc);
    expect(count($filtered))->toBe(3);
});

test('getTextNodes() ignores text inside <script>, <style> etc...', function () {
    $doc = Support::createDocument(<<<HTML
        Keep this
        <script>console.log("but drop this")</script>
        <style>.and-this {}</style>
        <svg>...</svg>
    HTML);
    $nodes = Support::getTextNodes($doc);
    expect(count($nodes))->toBe(1);
    expect(trim($nodes[0]->data))->toBe("Keep this");
});
