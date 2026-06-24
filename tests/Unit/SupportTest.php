<?php

use Dom\Text;
use Dom\XPath;
use Hirasso\HTMLObfuscator\Support\Support;

test('parseHtml() preserves leading and trailing whitespace', function () {
    $doc = Support::createDocument('<p>hello</p>');
    $xp = new XPath($doc);
    $node = $xp->query('//text()')->item(0);

    assert($node instanceof Text);

    $node->data = ' <span>hello</span> ';
    $parsed = Support::parseHtml($node->data);

    expect(count($parsed->childNodes ?? []))->toBe(3);
});

test('parseHtml() preserves leading and trailing white space', function () {
    $doc = Support::createDocument('<p>hello</p>');
    $xp = new XPath($doc);
    $node = $xp->query('//text()')->item(0);

    assert($node instanceof Text);

    $node->data = ' <em>please</em> preserve the whitespace ';
    $parsed = Support::parseHtml($node->data);

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
