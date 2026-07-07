<?php

use Hirasso\HTMLObfuscator\Obfuscation\Obfuscator;

test('Obfuscator::STRATEGIES keys match client decoder names', function () {
    expect(array_keys(Obfuscator::STRATEGIES))->toBe(['xor', 'revxor', 'rot47']);
});

test('Obfuscator::getAttribute() produces "index:data" format', function () {
    $obfuscation = new Obfuscator();
    $attr = $obfuscation->obfuscate('mail@example.com');

    expect($attr)->toMatch('/^\d+:.+$/s');

    [$index, $data] = explode(':', $attr, 2);
    expect((int) $index)->toBeGreaterThanOrEqual(0);
    expect((int) $index)->toBeLessThan(count(Obfuscator::STRATEGIES));
    expect($data)->not->toBeEmpty();
});
