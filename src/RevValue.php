<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscatedValue;

final readonly class RevValue implements ObfuscatedValue
{
    public function __construct(
        public string $original,
    ) {
    }

    public function getAttribute(): string
    {
        return 'rev:' . $this->encode();
    }

    public function encode(): string
    {
        return base64_encode(strrev($this->original));
    }
}
