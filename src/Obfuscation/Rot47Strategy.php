<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

// Shifts each printable ASCII character (codes 33–126) forward by 47 positions,
// wrapping around within that range. Non-printable characters are left unchanged.
// Applying the same operation twice restores the original string.
final readonly class Rot47Strategy implements ObfuscationStrategy
{
    public static function obfuscate(string $value): string
    {
        $result = '';
        for ($i = 0, $len = strlen($value); $i < $len; $i++) {
            $c = ord($value[$i]);
            $result .= ($c >= 33 && $c <= 126)
                ? chr(33 + ($c - 33 + 47) % 94)
                : $value[$i];
        }
        return $result;
    }
}
