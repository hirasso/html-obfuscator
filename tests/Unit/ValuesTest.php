<?php

use Hirasso\HTMLObfuscator\XORValue;
use Hirasso\HTMLObfuscator\RevValue;

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
