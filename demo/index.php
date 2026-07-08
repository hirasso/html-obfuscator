<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use function Hirasso\HTMLObfuscator\clientScript;

$currentYear = date('Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HTML Obfuscator</title>
  <meta name="description" content="Obfuscate emails, phone numbers, and other sensitive data in PHP. Invisible to humans, hidden from crawlers and headless bots until they interact."></meta>
  <meta property="og:image" content="/og-image.jpg">
  <?= clientScript() ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
  <link rel="stylesheet" href="demo.css">
</head>
<body>
    <script>
        window.addEventListener('html-obfuscator:reveal', () => {
            console.log('revealed')
        }, { once: true });
    </script>
  <main class="container-fluid">

    <header>
        <hgroup>
            <h1>HTML Obfuscator</h1>
            <p>
                <strong>
                    Obfuscate emails, phone numbers, and other sensitive data in PHP. Invisible to humans, hidden from crawlers and headless bots until they interact.
                </strong>
            </p>
            <p><strong>&rarr; <a href="https://github.com/hirasso/html-obfuscator">github.com/hirasso/html-obfuscator</a></strong></p>
        </hgroup>
    </header>

    <?php include('./readme.generated.html'); ?>

  </main>
  <footer class="container-fluid">
    <small>
        Browse the source on <a href="https://github.com/hirasso/html-obfuscator">GitHub</a>.
        Motivated by <a href="https://spencermortensen.com/articles/email-obfuscation/">this article</a> by Spencer Mortensen.
        Demo page built using <a href="https://picocss.com/">Pico CSS</a>.
        © <?= $currentYear ?> by <a href="https://github.com/hirasso">Rasso Hilber</a> 👋
    </small>
  </footer>
</body>
</html>
