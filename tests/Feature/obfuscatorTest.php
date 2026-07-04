<?php

use Dom\HTMLElement;
use Hirasso\HTMLObfuscator\HTMLObfuscator;
use Hirasso\HTMLObfuscator\ObfuscatorConfig;

use function Hirasso\HTMLObfuscator\clientScript;

const OBFUSCATE_TEXT_ATTR = HTMLObfuscator::OBFUSCATE_TEXT_ATTR;

afterEach(fn () => ObfuscatorConfig::reset());

test('Obfuscates emails in links', function () {
    expectObfuscatedElement(
        (string) obfuscate('<a href="mailto:mail@example.com">email</a>'),
        TESTS_TAG_NAME,
        customAttributes: ['attr']
    );
});

test('Keeps the scheme in the href for consistent styling', function () {
    $doc = obfuscate(<<<HTML
        <a href="mailto:mail@example.com">email</a><a href="tel:+49 12 345 67">tel</a>
        HTML)
        ->saveDocument();
    $links = $doc->querySelectorAll('a');

    expect($links->item(0)?->getAttribute('href'))->toBe("mailto:");
    expect($links->item(1)?->getAttribute('href'))->toBe("tel:");
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
    $result = (string) obfuscate('')->debug(false)->injectClientScript(true);
    expect($result)->toContain('new CustomEvent(n(t),{bubbles:!0})');
});

test('debug(false) injects the un-obfuscated frontend script', function () {
    $result = (string) obfuscate('')
        ->injectClientScript(true);

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

test('obfuscate() outputs full HTML for non-partial input', function () {
    $result = (string) HTMLObfuscator::createFromString('<html><body><p>hello</p></body></html>');
    expect($result)->toContain('<html');
    expect($result)->toContain('<p>hello</p>');
});

test('__toString() returns the rendered output', function () {
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

test("Allows to customize the custom element's tag name", function () {
    $result = HTMLObfuscator::createFromString('mail@example.com')
        ->withTagName('reveal-me')
        ->injectClientScript(false)
        ->saveDocument()
        ->querySelector('reveal-me');

    expect($result)->toBeInstanceOf(HTMLElement::class);
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

test('saveDocument() returns the underlying HTMLDocument', function () {
    $obfuscator = obfuscate('mail@example.com');
    expect($obfuscator->saveDocument())->toBeInstanceOf(\Dom\HTMLDocument::class);
});

test('obfuscates emails and phone numbers within the same text node', function () {
    $result = (string) obfuscate('this is an email: mail@example.com and a phone number: +49 12 345 67');
    expect(mb_substr_count($result, '<tests-obfuscated'))->toBe(2);
    expect($result)->not->toContain('mail@example.com');
    expect($result)->not->toContain('+49 12 345 67');
});

test('addRegex() obfuscates matching text nodes', function () {

    $result = (string) obfuscate('<span>verylongemail@</span>example.com')
        ->addRegex('/[^\s@]+@/') // obfuscates the <span> text node ("verylongemailaddress@")
        ->addRegex('/[^\s.]+(\.[^\s.]+)*\.[^\s.]{2,}/') // obfuscates the domain part ("example.com")
    ;

    expect(mb_substr_count($result, '<tests-obfuscated'))->toBe(2);
    expect($result)->not->toContain('example.com');
    expect($result)->not->toContain('verylongemail@');
});

test('addRegex() stacks multiple patterns', function () {
    $result = (string) obfuscate('foo-123 and bar-456')
        ->addRegex('/foo-\d+/')
        ->addRegex('/bar-\d+/');
    expect(mb_substr_count($result, '<tests-obfuscated'))->toBe(2);
    expect($result)->not->toContain('foo-123');
    expect($result)->not->toContain('bar-456');
});

test('addRegex() throws on invalid pattern', function () {
    expect(fn () => obfuscate('hello')->addRegex('not-a-valid-regex['))
        ->toThrow(InvalidArgumentException::class);
});

test('[obfuscate-text] obfuscates all text nodes inside', function () {
    $result = (string) obfuscate('<div obfuscate-text><span>foobar@</span>example.com</div>');
    expect(mb_substr_count($result, '<' . TESTS_TAG_NAME))->toBe(2);
    expect($result)->not->toContain('foobar@');
    expect($result)->not->toContain('example.com');
});

test('[obfuscate-text] removes the attribute from the element', function () {
    $result = (string) obfuscate('<div obfuscate-text>hello</div>');
    expect($result)->not->toContain('obfuscate-text');
});

test('[obfuscate-text] honors the exclusion list', function () {
    $result = (string) obfuscate('<div obfuscate-text><pre>dont obfuscate me</pre>visible text</div>');
    expect($result)->toContain('dont obfuscate me');
    expect(mb_substr_count($result, '<' . TESTS_TAG_NAME))->toBe(1);
});

test('[obfuscate-text] does not double-obfuscate with the pattern-matching pass', function () {
    $result = (string) obfuscate('<div obfuscate-text>mail@example.com</div>');
    expect(mb_substr_count($result, '<' . TESTS_TAG_NAME))->toBe(1);
});

test('[obfuscate-text] skips whitespace-only text nodes', function () {
    $result = (string) obfuscate("<div obfuscate-text>\n  <span>hello</span>\n</div>");
    expect(mb_substr_count($result, '<' . TESTS_TAG_NAME))->toBe(1);
});

test('renders the client script', function () {
    $debug_result = (string) clientScript()->withTagName(TESTS_TAG_NAME)->debug(true);
    expect($debug_result)->toContain('<script data-tagname="tests-obfuscated">');
    expect($debug_result)->toContain('/*! hirasso/html-obfuscator | MIT License');

    $minified_result = (string) clientScript()->withTagName(TESTS_TAG_NAME);
    expect($minified_result)->toContain('<script data-tagname="tests-obfuscated">');
    expect($minified_result)->not->toContain('/*! hirasso/html-obfuscator | MIT License');
});
