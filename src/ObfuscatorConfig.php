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
    private static ?string $key = null;
    private static string $tagName = HTMLObfuscator::DEFAULT_TAG_NAME;
    public static bool $hasInjectedClientScript = false;

    private function __construct()
    {
    }

    public static function setKey(string $value): void
    {
        if (self::$key !== null && $value !== self::$key) {
            throw new InvalidArgumentException('The key needs to be globally stable');
        }
        self::$key = $value;
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
        self::$key = null;
        self::$tagName = HTMLObfuscator::DEFAULT_TAG_NAME;
        self::$hasInjectedClientScript = false;
    }
}
