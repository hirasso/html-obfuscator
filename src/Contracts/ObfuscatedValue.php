<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Contracts;

interface ObfuscatedValue
{
    /** Encode the original value */
    public function encode(): string;

    /** Return the value attribute string for the custom element */
    public function getAttribute(): string;
}
