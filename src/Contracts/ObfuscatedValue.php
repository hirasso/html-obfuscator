<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Contracts;

interface ObfuscatedValue
{
    public function __construct(string $original, string $key);

    public function getAttribute(): string;
}
