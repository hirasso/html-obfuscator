<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class RevStrategy implements ObfuscationStrategy
{
    public function __construct(
        public string $original,
    ) {
    }

    public function getAttribute(): string
    {
        return 'rev:' . $this->obfuscate();
    }

    public function obfuscate(): string
    {
        return base64_encode(strrev($this->original));
    }
}
