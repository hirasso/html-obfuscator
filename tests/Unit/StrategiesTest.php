<?php

use Hirasso\HTMLObfuscator\Strategies\Strategy;

test('Strategy::STRATEGIES keys match client decoder names', function () {
    expect(array_keys(Strategy::STRATEGIES))->toBe(['xor', 'rev', 'rot47']);
});

test('Strategy::getAttribute() produces "index:data" format', function () {
    $strategy = new Strategy('mail@example.com');
    $attr = base64_decode($strategy->getAttribute());

    expect($attr)->toMatch('/^\d+:.+$/');

    [$index, $data] = explode(':', $attr, 2);
    expect((int) $index)->toBeGreaterThanOrEqual(0);
    expect((int) $index)->toBeLessThan(count(Strategy::STRATEGIES));
    expect($data)->not->toBeEmpty();
});
