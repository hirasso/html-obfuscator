<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Obfuscation;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class RevStrategy implements ObfuscationStrategy
{
    public static function obfuscate(string $value): string
    {
        return strrev($value);
    }
}
