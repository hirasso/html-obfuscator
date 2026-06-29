<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator\Enum;

enum Regex: string
{
    case Email = '[^\s@]+@[^\s@]+\.[^\s@]{2,}';
    case PhoneNumber = '[\+\d][\d \-\(\)\.]{6,20}(?<!\s)';

    /**
     * Get enabled Regexes
     * @return list<Regex>
     */
    public static function get(bool $emails, bool $phoneNumbers): array
    {
        $regexes = [];

        if ($emails) {
            $regexes[] = self::Email;
        }

        if ($phoneNumbers) {
            $regexes[] = self::PhoneNumber;
        }

        return $regexes;
    }
};
