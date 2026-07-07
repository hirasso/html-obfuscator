<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use Hirasso\HTMLObfuscator\HTMLObfuscator;
use Hirasso\HTMLObfuscator\Obfuscation\Obfuscator;

use function Hirasso\HTMLObfuscator\obfuscate;

/** render a nav link */
function navLink(string $label, string $href, bool $active): string
{
    $ariaCurrent = $active ? ' aria-current="page"' : '';
    return "<a href=\"{$href}\"{$ariaCurrent}>{$label}</a>";
}

$defaultTagName = HTMLObfuscator::DEFAULT_TAG_NAME;
$strategyKey = $_GET['strategy'] ?? null;
$strategyClass = Obfuscator::STRATEGIES[$strategyKey] ?? null;

$nav = implode('', [
    navLink('random', '/', $strategyKey === null),
    ...array_map(
        fn (string $key) => navLink($key, "/?strategy={$key}", $key === $strategyKey),
        array_keys(Obfuscator::STRATEGIES)
    ),
]);

$obfuscator = obfuscate(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Fixture</title>
  <style>
    {$defaultTagName}, a:has({$defaultTagName}[attr="href"]) { color: red }
    body {
        font-family: system-ui;
        --rounded: 0.5rem;
        margin: 1rem;
        max-width: 500px;
    }
    header {
        margin-bottom: 1rem;
    }
    h1 {
        font-size: inherit;
    }
    nav {
        display: flex;
        gap: 0.5rem;
        font-family: monospace;
    }
    nav a { padding: 0.25rem 0.5rem; text-decoration: none; color: inherit; border: 1px solid #ccc; border-radius: 3px }
    nav a[aria-current] { background: #333; color: #fff; border-color: #333 }
  </style>
</head>
<body>
    <header>
        <h1>HTMLObfuscator e2e fixtures</h1>
        <nav>$nav</nav>
    </header>
    <main>
        <p>This is a plaintext email address: mail@example.com. It should not cause layout shift when de-obfuscated.</p>
        <p>contact@example.com</p>
        <p>This is a plaintext phone number: +1 555 123 4567. It should not cause layout shift when de-obfuscated.</p>
        <p>+1 555 123-4567</p>
        <a href="mailto:mail@example.com">Send email</a>
    </main>
</body>
</html>
HTML);

if ($strategyClass !== null) {
    $obfuscator->setStrategy($strategyClass);
}

echo $obfuscator;
