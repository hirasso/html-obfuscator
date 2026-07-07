<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class RevXorStrategy implements ObfuscationStrategy
{
    public static function obfuscate(string $value): string
    {
        $reversed = strrev($value);
        $out = '';
        for ($i = 0, $len = strlen($reversed); $i < $len; $i++) {
            $out .= chr(ord($reversed[$i]) ^ ($i % 6 + 1));
        }
        return $out;
    }
}
