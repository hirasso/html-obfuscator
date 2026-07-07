<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

/**
 * Reverses the string, then XORs each byte with a position-dependent key (i % 6 + 1).
 * The JS decoder XORs with the same key sequence and reverses to recover the original.
 */
final readonly class RevXorStrategy implements ObfuscationStrategy
{
    public static function obfuscate(string $value): string
    {
        $reversed = strrev($value);
        $out = '';
        for ($i = 0, $len = strlen($reversed); $i < $len; $i++) {
            $out .= chr((ord($reversed[$i]) ^ ($i % 6 + 1)) & 0xFF);
        }
        return $out;
    }
}
