<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Strategies;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class RevStrategy implements ObfuscationStrategy
{
    public function __construct(
        public string $original,
    ) {
    }

    public function obfuscate(): string
    {
        return strrev($this->original);
    }
}
