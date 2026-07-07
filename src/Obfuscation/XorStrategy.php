<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class XorStrategy implements ObfuscationStrategy
{
    public static function obfuscate(string $value): string
    {
        $key = random_bytes(16);

        $out = '';
        for ($i = 0; $i < mb_strlen($value); $i++) {
            $out .= mb_substr($value, $i, 1) ^ $key[$i % 16];
        }
        return $key . $out;
    }
}
