<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

/**
 * XORs each byte of the value against a random 16-byte key (cycling the key as needed),
 * then prepends the key to the output so the JS decoder can reverse it.
 */
final readonly class XorStrategy implements ObfuscationStrategy
{
    public static function obfuscate(string $value): string
    {
        $key = random_bytes(16);

        $out = '';
        for ($i = 0, $len = strlen($value); $i < $len; $i++) {
            $out .= $value[$i] ^ $key[$i % 16];
        }
        return $key . $out;
    }
}
