<?php

use Hirasso\HTMLObfuscator\XORValue;
use Hirasso\HTMLObfuscator\RevValue;
use Hirasso\HTMLObfuscator\ROT47Value;

test('XORValue getAttribute() has the correct format', function () {
    $value = new XORValue('mail@example.com');
    [$strategy, $data] = explode(':', $value->getAttribute(), 2);
    expect($strategy)->toBe('xor');
    $decoded = base64_decode($data);
    expect(strlen($decoded))->toBeGreaterThan(16);
});

test('RevValue getAttribute() has the correct format', function () {
    $value = new RevValue('mail@example.com');
    [$strategy, $data] = explode(':', $value->getAttribute(), 2);
    expect($strategy)->toBe('rev');
    expect(base64_decode($data))->not->toBe('');
});

test('ROT47Value getAttribute() has the correct format', function () {
    $value = new ROT47Value('mail@example.com');
    [$strategy, $data] = explode(':', $value->getAttribute(), 2);
    expect($strategy)->toBe('rot47');
    expect(base64_decode($data))->not->toBe('');
});

test('ROT47Value is self-inverse', function () {
    $original = 'mail@example.com';
    $encoded = base64_decode((new ROT47Value($original))->encode());
    $roundtrip = '';
    for ($i = 0, $len = strlen($encoded); $i < $len; $i++) {
        $c = ord($encoded[$i]);
        $roundtrip .= ($c >= 33 && $c <= 126) ? chr(33 + ($c - 33 + 47) % 94) : $encoded[$i];
    }
    expect($roundtrip)->toBe($original);
});
