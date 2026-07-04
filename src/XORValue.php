<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscatedValue;

final readonly class XORValue implements ObfuscatedValue
{
    public function __construct(
        private string $original,
    ) {
    }

    public function getAttribute(): string
    {
        return "xor:{$this->encode()}";
    }

    public function encode(): string
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
