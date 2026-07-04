<?php

use Hirasso\HTMLObfuscator\XORStrategy;
use Hirasso\HTMLObfuscator\RevStrategy;
use Hirasso\HTMLObfuscator\ROT47Strategy;

test('XORStrategy getAttribute() has the correct format', function () {
    $value = new XORStrategy('mail@example.com');
    [$strategy, $data] = explode(':', $value->getAttribute(), 2);
    expect($strategy)->toBe('xor');
    $decoded = base64_decode($data);
    expect(strlen($decoded))->toBeGreaterThan(16);
});

test('RevStrategy getAttribute() has the correct format', function () {
    $value = new RevStrategy('mail@example.com');
    [$strategy, $data] = explode(':', $value->getAttribute(), 2);
    expect($strategy)->toBe('rev');
    expect(base64_decode($data))->not->toBe('');
});

test('ROT47Strategy getAttribute() has the correct format', function () {
    $value = new ROT47Strategy('mail@example.com');
    [$strategy, $data] = explode(':', $value->getAttribute(), 2);
    expect($strategy)->toBe('rot47');
    expect(base64_decode($data))->not->toBe('');
});

test('ROT47Strategy is self-inverse', function () {
    $original = 'mail@example.com';
    $encoded = base64_decode((new ROT47Strategy($original))->obfuscate());
    $roundtrip = '';
    for ($i = 0, $len = strlen($encoded); $i < $len; $i++) {
        $c = ord($encoded[$i]);
        $roundtrip .= ($c >= 33 && $c <= 126) ? chr(33 + ($c - 33 + 47) % 94) : $encoded[$i];
    }
    expect($roundtrip)->toBe($original);
});
