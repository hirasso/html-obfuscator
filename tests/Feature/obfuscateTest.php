<?php

use Hirasso\HTMLObfuscator\Enum\Interaction;
use Hirasso\HTMLObfuscator\HTMLObfuscator;

function obfuscate(string $html, bool $injectJS = false): HTMLObfuscator
{
    HTMLObfuscator::$hasInjectedFrontendScript = false;

    return HTMLObfuscator::createFromString($html)
        ->withPassphrase('testing')
        ->randomizeKey(false)
        ->injectFrontendScript($injectJS);
}

function render(string $html, bool $injectJS = false): string
{
    return obfuscate(...func_get_args())->render();
}

/** @param list<string> $customAttributes */
function expectObfuscatedElement(
    string $html,
    string $elementName = HTMLObfuscator::DEFAULT_TAG_NAME,
    array $customAttributes = []
): void {
    expect($html)->toContain("<$elementName ");
    expect($html)->toContain("</$elementName>");

    foreach (['value', 'key', ...$customAttributes] as $attr) {
        expect($html)->toContain($attr . '="');
    }
}

test('Obfuscates emails in links', function () {
    expectObfuscatedElement(render('< href="mailto:mail@example.com">email</a>'));
});

test('Obfuscates emails in plaintext', function () {
    expectObfuscatedElement(render('mail@example.com'));
});

test('Obfuscates phone numbers in links', function () {
    expectObfuscatedElement(render('<a href="tel:+49 12 345 67">call us</a>'));
});

test('Obfuscates phone numbers in plaintext', function () {
    expectObfuscatedElement(render('+49 12 345 67'));
});

test('Injects the terser-obfuscated frontend script by default', function () {
    $result = render('+49 12 345 67', injectJS: true);

    expect($result)->toContain('ar __defProp=Object.defineProperty');
});

test('obfuscateFrontendScript(false) loads the un-obfuscated frontend script', function () {
    $result = obfuscate('+49 12 345 67', injectJS: true)
        ->obfuscateFrontendScript(false)
        ->render();

    expect($result)->toContain('@ts-check');
});

test('emails(false) disables email obfuscation', function () {
    $result = HTMLObfuscator::createFromString('<a href="mailto:mail@example.com">email</a>')
        ->emails(false)
        ->injectFrontendScript(false)
        ->render();
    expect($result)->toContain('mailto:mail@example.com');
    expect($result)->not->toContain('x-obfuscated');
});

test('phoneNumbers(false) disables phone number obfuscation', function () {
    $result = obfuscate('<a href="tel:+49 12 345 67">call us</a>')
        ->phoneNumbers(false)
        ->injectFrontendScript(false)
        ->render();
    expect($result)->toContain('tel:+49 12 345 67');
    expect($result)->not->toContain('x-obfuscated');
});

test('randomizeKey(true) produces obfuscated output', function () {
    $result = obfuscate('mail@example.com')
        ->withPassphrase('testing')
        ->randomizeKey(true)
        ->injectFrontendScript(false)
        ->render();
    expect($result)->toContain('<x-obfuscated');
});

test('render() outputs full HTML for non-partial input', function () {
    HTMLObfuscator::$hasInjectedFrontendScript = false;
    $result = HTMLObfuscator::createFromString('<html><body><p>hello</p></body></html>')
        ->injectFrontendScript(false)
        ->render();
    expect($result)->toContain('<html');
    expect($result)->toContain('<p>hello</p>');
});

test('__toString() returns the rendered output', function () {
    HTMLObfuscator::$hasInjectedFrontendScript = false;
    $obfuscate = HTMLObfuscator::createFromString('hello world')
        ->injectFrontendScript(false);
    expect((string) $obfuscate)->toBe('hello world');
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
    $result = obfuscate('mail@example.com', injectJS: true)
        ->withTagName('reveal-me')
        ->obfuscateFrontendScript(false)
        ->render();

    dump($result);

    expectObfuscatedElement($result, 'reveal-me');
    expect($result)->toContain('const tagName = "reveal-me"');
});

test('Exposes ->apply() as public method', function () {
    $obfuscate = obfuscate('mail@example.com', injectJS: true)->apply();
    expect($obfuscate)->toBeInstanceOf(HTMLObfuscator::class);
});

test('withTagName() throws if the element name is malformed', function () {
    expect(fn () => obfuscate('mail@example.com')->withTagName('foobar'))
        ->toThrow(InvalidArgumentException::class);
});

test('Returns a partial when receiving a partial', function () {
    expect(render('foobar'))->toBe('foobar');
});

test('Returns a the full document when receiving at least a <body> element', function () {
    expect(render('<body>foobar</body>'))->toBe('<html><head></head><body>foobar</body></html>');
});

test('Adds the attribute "require-interaction" if needed', function () {
    $result = obfuscate('mail@example.com')
        ->requireInteraction(Interaction::OnDocument)
        ->render();

    expect($result)->toContain('require-interaction');
});
