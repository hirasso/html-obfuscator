<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class XORStrategy implements ObfuscationStrategy
{
    public function __construct(
        private string $original,
    ) {
    }

    public function getAttribute(): string
    {
        return "xor:{$this->obfuscate()}";
    }

    public function obfuscate(): string
    {
        $value = $this->original;
        $key = random_bytes(16);

        $out = '';
        for ($i = 0; $i < mb_strlen($value); $i++) {
            $out .= mb_substr($value, $i, 1) ^ $key[$i % 16];
        }
        return base64_encode($key . $out);
    }
}
