<?php

use Hirasso\HTMLObfuscator\HTMLObfuscator;

require_once(dirname(__DIR__) . '/vendor/autoload.php');


function dumpEnvironment(): void
{
    $phpVersion = PHP_VERSION;
    $libxmlVersion = LIBXML_DOTTED_VERSION;
    $isCI = getenv('CI') === 'true';
    echo "----------------------------------------------------" . PHP_EOL;
    dump(compact('phpVersion', 'libxmlVersion', 'isCI'));
    echo "----------------------------------------------------" . PHP_EOL;
}
dumpEnvironment();

const TESTS_PASSPHRASE = 'test';
const TESTS_TAG_NAME = 'tests-obfuscated';

/**
 * Obfuscate with sensible defaults for tests.
 * Use this instead of HTMLObfuscator::createFromString.
 *
 * Customize/Override options on a per-test basis by chaining them like so:
 *
 * obfuscate('value')->debug(false)
 */
function obfuscate(string $html): HTMLObfuscator
{
    return HTMLObfuscator::createFromString($html, TESTS_PASSPHRASE)
        ->withTagName(TESTS_TAG_NAME)
        ->injectClientScript(false)
        ->debug(true);
}

/** @param list<string> $customAttributes */
function expectObfuscatedElement(
    string $html,
    string $elementName = TESTS_TAG_NAME,
    array $customAttributes = []
): void {
    expect($html)->toContain("<$elementName ");
    expect($html)->toContain("</$elementName>");

    foreach (['value', ...$customAttributes] as $attr) {
        expect($html)->toContain($attr . '="');
    }
}
