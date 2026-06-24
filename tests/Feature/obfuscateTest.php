<?php

use Hirasso\HTMLObfuscator\HTMLObfuscator;

function apply(string $string, bool $injectJS = false): string
{
    HTMLObfuscator::$jsInjected = false;

    return HTMLObfuscator::createFromString($string)
        ->passphrase('testing')
        ->randomizeKey(false)
        ->injectDeobfuscationScript($injectJS)
        ->render();
}

test('Obfuscates emails in links', function () {
    $result = apply('<a href="mailto:mail@example.com">email</a>');

    expect($result)->toBe('<html-obfuscator-obfuscated value="XQQSCkMDBVwXXFRQWE0KDwlUXQoiV0oDVRUIXBtWWFhDW1cPUA8PXRpQCw==" key="ae2b1fca515949e5d54fb22b8ed95575" type="element"></html-obfuscator-obfuscated>');
});

test('Obfuscates emails in plaintext', function () {
    $result = apply('mail@example.com');

    expect($result)->toBe('<html-obfuscator-obfuscated value="DARbDnEDGwBYQVlcGloKWA==" key="ae2b1fca515949e5d54fb22b8ed95575"></html-obfuscator-obfuscated>');
});

test('Obfuscates phone numbers in links', function () {
    $result = apply('<a href="tel:+49 12 345 67">call us</a>');

    expect($result)->toBe('<html-obfuscator-obfuscated value="XQQSCkMDBVwXRVBVDhJRDEQEBkZRBgdCDlJGB1ZUW1lBEEFeHgdd" key="ae2b1fca515949e5d54fb22b8ed95575" type="element"></html-obfuscator-obfuscated>');
});

test('Obfuscates phone numbers in plaintext', function () {
    $result = apply('+49 12 345 67');

    expect($result)->toBe('<html-obfuscator-obfuscated value="SlELQgBUQ1IBBBUPAw==" key="ae2b1fca515949e5d54fb22b8ed95575"></html-obfuscator-obfuscated>');
});

test('Injects the deobfuscation JavaScript by default', function () {
    $result = apply('+49 12 345 67', injectJS: true);

    expect($result)->toContain('<script');
    expect($result)->toContain('@ts-check');
    expect($result)->toContain('class ObfuscatedElement extends HTMLElement');
});
