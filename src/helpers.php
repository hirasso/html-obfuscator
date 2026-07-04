<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

/**
 * Obfuscate a HTML string or \Dom\HTMLDocument
 */
function obfuscate(string|\Dom\HTMLDocument $source): HTMLObfuscator
{
    return match(true) {
        is_string($source) => HTMLObfuscator::createFromString($source),
        default => HTMLObfuscator::createFromDocument($source)
    };
}

/**
 * Render the client script for manual placement (e.g. in the <head>)
 */
function clientScript(): HTMLObfuscator
{
    return HTMLObfuscator::createForClientScript();
}
