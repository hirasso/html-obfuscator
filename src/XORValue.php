<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscatedValue;

final readonly class XORValue implements ObfuscatedValue
{
    public string $encoded;

    public function __construct(
        public string $original,
        public string $key
    ) {
        $this->encoded = $this->encode($original, $key);
    }

    public function getAttribute(): string
    {
        return "{$this->encoded}:xor:{$this->key}";
    }

    private function encode(string $value, string $key): string
    {
        $out = '';
        for ($i = 0; $i < mb_strlen($value); $i++) {
            $out .= mb_substr($value, $i, 1) ^ mb_substr($key, $i % mb_strlen($key), 1);
        }
        return base64_encode($out);
    }
}
