<?php

use Dom\HTMLDocument;
use Hirasso\HTMLObfuscator\HTMLObfuscator;

uses(PHPUnit\Framework\TestCase::class);

test('Named constructors return an HTMLObfuscator instance', function () {
    expect(HTMLObfuscator::createFromDocument(HTMLDocument::createEmpty())::class)->toBe(HTMLObfuscator::class);
    expect(HTMLObfuscator::createFromString('foo bar baz')::class)->toBe(HTMLObfuscator::class);
});
