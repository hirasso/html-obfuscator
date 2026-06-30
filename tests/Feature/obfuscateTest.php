<?php

use Hirasso\HTMLObfuscator\HTMLObfuscator;

const TESTS_TAG_NAME = 'tests-obfuscated';

/**
 * Obfuscate with sensible defaults for tests.
 * Use this instead of HTMLObfuscator::createFromString.
 *
 * Customize/Override options on a per-test basis by chaining them like so:
 *
 * obfuscate('value')->withPassphrase('my-custom-passphrase')
 */
function obfuscate(string $html): HTMLObfuscator
{
    HTMLObfuscator::$hasInjectedFrontendScript = false;

    return HTMLObfuscator::createFromString($html)
        ->withPassphrase('testing')
        ->randomizeKey(false)
        ->withTagName(TESTS_TAG_NAME)
        ->injectFrontendScript(false)
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

    foreach (['value', 'key', ...$customAttributes] as $attr) {
        expect($html)->toContain($attr . '="');
    }
}

test('Obfuscates emails in links', function () {
    expectObfuscatedElement((string) obfuscate('<a href="mailto:mail@example.com">email</a>'));
});

test('Obfuscates emails in plaintext', function () {
    expectObfuscatedElement((string) obfuscate('mail@example.com'));
});

test('Obfuscates phone numbers in links', function () {
    expectObfuscatedElement((string) obfuscate('<a href="tel:+49 12 345 67">call us</a>'));
});

test('Obfuscates phone numbers in plaintext', function () {
    expectObfuscatedElement((string) obfuscate('+49 12 345 67'));
});

test('debug(false) Injects the minified and mangled frontend script', function () {
    $result = (string) obfuscate('')->debug(false)->injectFrontendScript(true);
    expect($result)->toContain('(function(){var e={tagName:`tests-obfuscated`');
});

test('debug(false) injects the un-obfuscated frontend script', function () {
    $result = (string) obfuscate('')
        ->injectFrontendScript(true);

    expect($result)->toContain('//#region resources/src/helpers.ts');
});

test('emails(false) disables email obfuscation', function () {
    $result = (string) obfuscate('<a href="mailto:mail@example.com">email</a>')
        ->emails(false);
    dump($result);
    expect($result)->toContain('mailto:mail@example.com');
    expect($result)->not->toContain('tests-obfuscated');
});

test('phoneNumbers(false) disables phone number obfuscation', function () {
    $result = (string) obfuscate('<a href="tel:+49 12 345 67">call us</a>')
        ->phoneNumbers(false);
    expect($result)->toContain('tel:+49 12 345 67');
    expect($result)->not->toContain('tests-obfuscated');
});

test('randomizeKey(true) produces obfuscated output', function () {
    $result = (string) obfuscate('mail@example.com')
        ->randomizeKey(true);
    expect($result)->toContain('<tests-obfuscated');
});

test('(string) obfuscate() outputs full HTML for non-partial input', function () {
    HTMLObfuscator::$hasInjectedFrontendScript = false;
    $result = HTMLObfuscator::createFromString('<html><body><p>hello</p></body></html>')
        ->render();
    expect($result)->toContain('<html');
    expect($result)->toContain('<p>hello</p>');
});

test('__toString() returns the rendered output', function () {
    HTMLObfuscator::$hasInjectedFrontendScript = false;
    $result = (string) obfuscate('hello world');
    expect($result)->toBe('hello world');
});

test('Invalid mailto: links are not obfuscated', function () {
    $result = (string) obfuscate('<a href="mailto:not-an-email">contact</a>');
    expect($result)->toContain('mailto:not-an-email');
    expect($result)->not->toContain('tests-obfuscated');
});

test('Invalid tel: links are not obfuscated', function () {
    $result = (string) obfuscate('<a href="tel:123">call</a>');
    expect($result)->toContain('tel:123');
    expect($result)->not->toContain('tests-obfuscated');
});

test('Allows to customize the custom element name', function () {
    $result = obfuscate('mail@example.com')
        ->withTagName('reveal-me')
        ->injectFrontendScript(true)
        ->render();

    dump($result);

    expectObfuscatedElement($result, 'reveal-me');
    expect($result)->toContain('tagName: "reveal-me"');
});

test('Exposes ->apply() as public method', function () {
    $obfuscate = obfuscate('mail@example.com');
    expect($obfuscate)->toBeInstanceOf(HTMLObfuscator::class);
});

test('withTagName() throws if the element name is malformed', function () {
    expect(fn () => obfuscate('mail@example.com')->withTagName('foobar'))
        ->toThrow(InvalidArgumentException::class);
});

test('Returns a partial when receiving a partial', function () {
    expect((string) obfuscate('foobar'))->toBe('foobar');
});

test('Returns a the full document when receiving at least a <body> element', function () {
    expect((string) obfuscate('<body>foobar</body>'))->toContain('<html><head></head><body>foobar');
});

test('getDocument() returns the underlying HTMLDocument', function () {
    $obfuscator = obfuscate('mail@example.com');
    expect($obfuscator->getDocument())->toBeInstanceOf(\Dom\HTMLDocument::class);
});

test('obfuscates emails and phone numbers within the same text node', function () {
    $result = (string) obfuscate('this is an email: mail@example.com and a phone number: +49 12 345 67');
    expect(mb_substr_count($result, '<tests-obfuscated'))->toBe(2);
    expect($result)->not->toContain('mail@example.com');
    expect($result)->not->toContain('+49 12 345 67');
});
