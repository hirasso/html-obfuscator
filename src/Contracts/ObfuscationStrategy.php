<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Contracts;

interface ObfuscationStrategy
{
    /** Apply obfuscation to the original value */
    public function obfuscate(): string;
}
