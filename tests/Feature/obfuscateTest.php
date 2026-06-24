<?php

use Dom\HTMLDocument;
use Hirasso\HTMLObfuscator\HTMLObfuscator;

function obfuscator(string $string, bool $injectJS = false): HTMLObfuscator
{
    HTMLObfuscator::$jsInjected = false;

    return HTMLObfuscator::createFromString($string)
        ->passphrase('testing')
        ->randomizeKey(false)
        ->injectDeobfuscationScript($injectJS);
}

function render(string $string, bool $injectJS = false): string
{
    return obfuscator(...func_get_args())->render();
}

test('Obfuscates emails in links', function () {
    $result = render('<a href="mailto:mail@example.com">email</a>');

    expect($result)->toBe('<obfuscated-element value="XQQSCkMDBVwXXFRQWE0KDwlUXQoiV0oDVRUIXBtWWFhDW1cPUA8PXRpQCw==" key="ae2b1fca515949e5d54fb22b8ed95575" type="element"></obfuscated-element>');
});

test('Obfuscates emails in plaintext', function () {
    $result = render('mail@example.com');

    expect($result)->toBe('<obfuscated-text value="DARbDnEDGwBYQVlcGloKWA==" key="ae2b1fca515949e5d54fb22b8ed95575"></obfuscated-text>');
});

test('Obfuscates phone numbers in links', function () {
    $result = render('<a href="tel:+49 12 345 67">call us</a>');

    expect($result)->toBe('<obfuscated-element value="XQQSCkMDBVwXRVBVDhJRDEQEBkZRBgdCDlJGB1ZUW1lBEEFeHgdd" key="ae2b1fca515949e5d54fb22b8ed95575" type="element"></obfuscated-element>');
});

test('Obfuscates phone numbers in plaintext', function () {
    $result = render('+49 12 345 67');

    expect($result)->toBe('<obfuscated-text value="SlELQgBUQ1IBBBUPAw==" key="ae2b1fca515949e5d54fb22b8ed95575"></obfuscated-text>');
});

test('Injects the deobfuscation JavaScript by default', function () {
    $result = render('+49 12 345 67', injectJS: true);

    expect($result)->toContain('<script');
    expect($result)->toContain('@ts-check');
    expect($result)->toContain('class ObfuscatedElement extends HTMLElement');
});

test('emails(false) disables email obfuscation', function () {
    $result = HTMLObfuscator::createFromString('<a href="mailto:mail@example.com">email</a>')
        ->emails(false)
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('mailto:mail@example.com');
    expect($result)->not->toContain('obfuscated-element');
});

test('phoneNumbers(false) disables phone number obfuscation', function () {
    $result = obfuscator('<a href="tel:+49 12 345 67">call us</a>')
        ->phoneNumbers(false)
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('tel:+49 12 345 67');
    expect($result)->not->toContain('obfuscated-element');
});

test('randomizeKey(true) produces obfuscated output', function () {
    $result = obfuscator('mail@example.com')
        ->passphrase('testing')
        ->randomizeKey(true)
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('<obfuscated-text');
});

test('getDocument() returns the underlying HTMLDocument', function () {
    $doc = HTMLDocument::createFromString('hello', LIBXML_NOERROR);
    $obfuscator = HTMLObfuscator::createFromDocument($doc);
    expect($obfuscator->getDocument())->toBe($doc);
});

test('render() outputs full HTML for non-partial input', function () {
    HTMLObfuscator::$jsInjected = false;
    $result = HTMLObfuscator::createFromString('<html><body><p>hello</p></body></html>')
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('<html');
    expect($result)->toContain('<p>hello</p>');
});

test('render() returns empty string for empty document', function () {
    HTMLObfuscator::$jsInjected = false;
    $result = HTMLObfuscator::createFromDocument(HTMLDocument::createEmpty())
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toBe('');
});

test('__toString() returns the rendered output', function () {
    HTMLObfuscator::$jsInjected = false;
    $obfuscator = HTMLObfuscator::createFromString('hello world')
        ->injectDeobfuscationScript(false);
    expect((string) $obfuscator)->toBe('hello world');
});

test('Invalid mailto: links are not obfuscated', function () {
    $result = render('<a href="mailto:not-an-email">contact</a>');
    expect($result)->toContain('mailto:not-an-email');
    expect($result)->not->toContain('obfuscated-element');
});

test('Invalid tel: links are not obfuscated', function () {
    $result = render('<a href="tel:123">call</a>');
    expect($result)->toContain('tel:123');
    expect($result)->not->toContain('obfuscated-element');
});
