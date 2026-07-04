<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscatedValue;

final readonly class XORValue implements ObfuscatedValue
{
    public function __construct(
        private string $original,
        private string $key
    ) {
    }

    public function getAttribute(): string
    {
        $encoded = $this->encode();
        return "xor:{$encoded}:{$this->key}";
    }

    public function encode(): string
    {
        $value = $this->original;
        $key = $this->key;

        $out = '';
        for ($i = 0; $i < mb_strlen($value); $i++) {
            $out .= mb_substr($value, $i, 1) ^ mb_substr($key, $i % mb_strlen($key), 1);
        }
        return base64_encode($out);
    }
}
