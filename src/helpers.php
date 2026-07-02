<?php

declare(strict_types=1);

namespace Hirasso\HTMLObfuscator;

/**
 * Obfuscate a HTML string or \Dom\HTMLDocument
 */
function obfuscate(string|\Dom\HTMLDocument $source, string $passphrase): HTMLObfuscator
{
    return match(true) {
        is_string($source) => HTMLObfuscator::createFromString($source, $passphrase),
        default => HTMLObfuscator::createFromDocument($source, $passphrase)
    };
}

/**
 * Render the client script for manual placement (e.g. in the <head>)
 */
function clientScript(string $passphrase): HTMLObfuscator
{
    return HTMLObfuscator::createForClientScript($passphrase);
}
