<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

/**
 * Obfuscate a HTML string or \Dom\HTMLDocument
 */
function obfuscate(string|\Dom\HTMLDocument $source, string $key): HTMLObfuscator
{
    return match(true) {
        is_string($source) => HTMLObfuscator::createFromString($source, $key),
        default => HTMLObfuscator::createFromDocument($source, $key)
    };
}

/**
 * Render the client script for manual placement (e.g. in the <head>)
 */
function clientScript(string $key): HTMLObfuscator
{
    return HTMLObfuscator::createForClientScript($key);
}
