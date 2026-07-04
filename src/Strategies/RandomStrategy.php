<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Strategies;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;
use Override;

final readonly class RandomStrategy implements ObfuscationStrategy
{
    /** Maps client-side decoder name → strategy class, in index order */
    public const array STRATEGIES = [
        'xor'   => XORStrategy::class,
        'rev'   => RevStrategy::class,
        'rot47' => ROT47Strategy::class,
    ];

    public int $index;
    public ObfuscationStrategy $strategy;

    public function __construct(
        private string $original,
    ) {
        $strategies = array_values(self::STRATEGIES);
        $this->index = (int) floor(rand(0, count($strategies) - 1));
        $this->strategy = new $strategies[$this->index]($original);
    }

    #[Override]
    public function obfuscate(): string
    {
        return $this->strategy->obfuscate();
    }

    public function getAttribute(): string
    {
        return "{$this->index}:{$this->strategy->obfuscate()}";
    }
}
