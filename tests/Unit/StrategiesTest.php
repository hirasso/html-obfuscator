<?php

use Hirasso\HTMLObfuscator\Strategies\RandomStrategy;

test('RandomStrategy::STRATEGIES keys match client decoder names', function () {
    expect(array_keys(RandomStrategy::STRATEGIES))->toBe(['xor', 'rev', 'rot47']);
});

test('RandomStrategy::getAttribute() produces "index:data" format', function () {
    $strategy = new RandomStrategy('mail@example.com');
    $attr = $strategy->getAttribute();

    expect($attr)->toMatch('/^\d+:.+$/');

    [$index, $data] = explode(':', $attr, 2);
    expect((int) $index)->toBeGreaterThanOrEqual(0);
    expect((int) $index)->toBeLessThan(count(RandomStrategy::STRATEGIES));
    expect($data)->not->toBeEmpty();
});
