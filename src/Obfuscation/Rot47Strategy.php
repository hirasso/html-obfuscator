<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

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
