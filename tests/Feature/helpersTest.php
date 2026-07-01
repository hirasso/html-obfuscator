<?php

use Dom\HTMLDocument;
use Hirasso\HTMLObfuscator\HTMLObfuscator;

use function Hirasso\HTMLObfuscator\obfuscate;

uses(PHPUnit\Framework\TestCase::class);

test('Named constructors return an HTMLObfuscator instance', function () {
    expect(HTMLObfuscator::createFromDocument(HTMLDocument::createEmpty(), 'test')::class)->toBe(HTMLObfuscator::class);
    expect(HTMLObfuscator::createFromString('foo bar baz', 'test')::class)->toBe(HTMLObfuscator::class);
});

test('obfuscate(HTMLDocument) mutates the passed HTMLDocument directly', function () {
    $doc = HTMLDocument::createEmpty();
    expect(obfuscate($doc, 'test')->getDocument())->toBe($doc);
});
