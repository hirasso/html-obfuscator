<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use Hirasso\HTMLObfuscator\Contracts\ObfuscatedValue;

final readonly class ROT47Value implements ObfuscatedValue
{
    public function __construct(
        private string $original,
    ) {
    }

    public function getAttribute(): string
    {
        return 'rot47:' . $this->encode();
    }

    public function encode(): string
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
