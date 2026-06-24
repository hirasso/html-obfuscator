<?php

use Hirasso\HTMLObfuscator\HTMLObfuscator;

function obfuscator(string $html, bool $injectJS = false): HTMLObfuscator
{
    HTMLObfuscator::$jsInjected = false;

    return HTMLObfuscator::createFromString($html)
        ->withPassphrase('testing')
        ->randomizeKey(false)
        ->injectDeobfuscationScript($injectJS);
}

function render(string $html, bool $injectJS = false): string
{
    return obfuscator(...func_get_args())->render();
}

test('Obfuscates emails in links', function () {
    $result = render('<a href="mailto:mail@example.com">email</a>');

    expect($result)->toBe('<x-obfuscated value="XQQSCkMDBVwXXFRQWE0KDwlUXQoiV0oDVRUIXBtWWFhDW1cPUA8PXRpQCw==" key="ae2b1fca515949e5d54fb22b8ed95575"></x-obfuscated>');
});

test('Obfuscates emails in plaintext', function () {
    $result = render('mail@example.com');

    expect($result)->toBe('<x-obfuscated value="DARbDnEDGwBYQVlcGloKWA==" key="ae2b1fca515949e5d54fb22b8ed95575"></x-obfuscated>');
});

test('Obfuscates phone numbers in links', function () {
    $result = render('<a href="tel:+49 12 345 67">call us</a>');

    expect($result)->toBe('<x-obfuscated value="XQQSCkMDBVwXRVBVDhJRDEQEBkZRBgdCDlJGB1ZUW1lBEEFeHgdd" key="ae2b1fca515949e5d54fb22b8ed95575"></x-obfuscated>');
});

test('Obfuscates phone numbers in plaintext', function () {
    $result = render('+49 12 345 67');

    expect($result)->toBe('<x-obfuscated value="SlELQgBUQ1IBBBUPAw==" key="ae2b1fca515949e5d54fb22b8ed95575"></x-obfuscated>');
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
    expect($result)->not->toContain('x-obfuscated');
});

test('phoneNumbers(false) disables phone number obfuscation', function () {
    $result = obfuscator('<a href="tel:+49 12 345 67">call us</a>')
        ->phoneNumbers(false)
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('tel:+49 12 345 67');
    expect($result)->not->toContain('x-obfuscated');
});

test('randomizeKey(true) produces obfuscated output', function () {
    $result = obfuscator('mail@example.com')
        ->withPassphrase('testing')
        ->randomizeKey(true)
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('<x-obfuscated');
});

test('render() outputs full HTML for non-partial input', function () {
    HTMLObfuscator::$jsInjected = false;
    $result = HTMLObfuscator::createFromString('<html><body><p>hello</p></body></html>')
        ->injectDeobfuscationScript(false)
        ->render();
    expect($result)->toContain('<html');
    expect($result)->toContain('<p>hello</p>');
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
    expect($result)->not->toContain('x-obfuscated');
});

test('Invalid tel: links are not obfuscated', function () {
    $result = render('<a href="tel:123">call</a>');
    expect($result)->toContain('tel:123');
    expect($result)->not->toContain('x-obfuscated');
});

test('Allows to customize the custom element name', function () {
    $result = obfuscator('mail@example.com', injectJS: true)
        ->withCustomElementName('reveal-me')
        ->render();

    expect($result)->not->toContain('<x-obfuscated value="');
    expect($result)->toContain('<reveal-me value="');
    expect($result)->toContain('window.customElements.define("reveal-me", ObfuscatedElement)');
});

test('Exposes ->apply() as public method', function () {
    $obfuscator = obfuscator('mail@example.com', injectJS: true)->apply();
    expect($obfuscator)->toBeInstanceOf(HTMLObfuscator::class);
});

test('withCustomElementName() throws if the element name is malformed', function () {
    expect(fn () => obfuscator('mail@example.com')->withCustomElementName('foobar'))
        ->toThrow(InvalidArgumentException::class);
});

test('Returns a partial when receiving a partial', function () {
    expect(render('foobar'))->toBe('foobar');
});

test('Returns a the full document when receiving at least a <body> element', function () {
    expect(render('<body>foobar</body>'))->toBe('<html><head></head><body>foobar</body></html>');
});
