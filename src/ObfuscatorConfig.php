<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

use InvalidArgumentException;

/**
 * The global config. Should only be set once
 *
 * @internal
 */
final class ObfuscatorConfig
{
    private static ?string $passphrase = null;
    private static string $tagName = HTMLObfuscator::DEFAULT_TAG_NAME;
    public static bool $hasInjectedClientScript = false;

    private function __construct()
    {
    }

    public static function setPassphrase(string $value): void
    {
        if (self::$passphrase !== null && $value !== self::$passphrase) {
            throw new InvalidArgumentException('The passphrase needs to be globally stable');
        }
        self::$passphrase = $value;
    }

    public static function getTagName(): string
    {
        return self::$tagName;
    }

    public static function setTagName(string $value): void
    {
        $value = trim($value);

        if (!str_contains($value, '-')) {
            throw new InvalidArgumentException('The tag name needs to contain at least one dash');
        }

        if (self::$tagName !== HTMLObfuscator::DEFAULT_TAG_NAME && $value !== self::$tagName) {
            throw new InvalidArgumentException('The tag name needs to be globally stable');
        }

        self::$tagName = $value;
    }

    public static function reset(): void
    {
        self::$passphrase = null;
        self::$tagName = HTMLObfuscator::DEFAULT_TAG_NAME;
        self::$hasInjectedClientScript = false;
    }
}
