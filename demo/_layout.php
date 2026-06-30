<?php

declare(strict_types=1);

use Hirasso\HTMLObfuscator\Enum\RevealTrigger;
use Hirasso\HTMLObfuscator\HTMLObfuscator;

function render_example(RevealTrigger $strategy): void
{
    $label = $strategy->value;
    $withPlaceholders = $strategy !== RevealTrigger::Load;

    $nav = implode('', array_map(
        fn (RevealTrigger $s) => sprintf(
            '<a href="strategy-%s.php"%s>%s</a>',
            $s->value,
            $s === $strategy ? ' class="active"' : '',
            $s->value,
        ),
        RevealTrigger::cases()
    ));

    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>html-obfuscator &mdash; {$label}</title>
        <style>
            body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; padding: 0 1rem; }
            nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
            nav a { text-decoration: none; color: #555; }
            nav a.active { font-weight: bold; color: #000; }
            table { border-collapse: collapse; width: 100%; }
            td { padding: .5rem .75rem; border: 1px solid #ddd; }
            td:first-child { color: #777; font-size: .85em; white-space: nowrap; }
        </style>
    </head>
    <body>
        <nav><a href="index.php">&larr; back</a>{$nav}</nav>
        <h1>strategy: {$label}</h1>
        <table>
            <tr>
                <td>email text</td>
                <td>mail@example.com</td>
            </tr>
            <tr>
                <td>email link</td>
                <td>
                    <a href="mailto:contact@example.com">contact@example.com</a>
                </td>
            </tr>
            <tr>
                <td>phone text</td>
                <td>+1 555 123 4567 and some more text</td>
            </tr>
            <tr>
                <td>phone link</td>
                <td>
                    <a href="tel:+15551234567">+1 555 123-4567</a>
                </td>
            </tr>
        </table>
    </body>
    </html>
    HTML;

    $obfuscator = HTMLObfuscator::createFromString($html)
        ->debug(true)
        ->revealOn($strategy);

    if ($withPlaceholders) {
        $obfuscator->renderPlaceholders();
    }

    echo $obfuscator->render();
}
