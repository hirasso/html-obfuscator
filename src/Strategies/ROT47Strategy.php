<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Strategies;

use Hirasso\HTMLObfuscator\Contracts\ObfuscationStrategy;

final readonly class ROT47Strategy implements ObfuscationStrategy
{
    public function __construct(
        private string $original,
    ) {
    }

    public function obfuscate(): string
    {
        $result = '';
        for ($i = 0, $len = strlen($this->original); $i < $len; $i++) {
            $c = ord($this->original[$i]);
            $result .= ($c >= 33 && $c <= 126)
                ? chr(33 + ($c - 33 + 47) % 94)
                : $this->original[$i];
        }
        return base64_encode($result);
    }
}
