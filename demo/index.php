<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use function Hirasso\HTMLObfuscator\obfuscate;
use function Hirasso\HTMLObfuscator\clientScript;

$contactHtml = obfuscate(<<<HTML
<p>You can reach me by email at <a href="mailto:mail@rassohilber.com">mail@rassohilber.com</a> or by phone at <a href="tel:+4917620020805">+49 176 200 20 805</a>.</p>
HTML)->debug(true);

$currentYear = date('Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HTML Obfuscator</title>
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
            <p><a href="https://github.com/hirasso/html-obfuscator" role="button" class="pill outline">hirasso/html-obfuscator</a></p>
            <h1>HTML Obfuscator</h1>
            <p><strong>Tl;dr</strong>: Make crawlers earn it. Obfuscates emails, phone numbers, and other sensitive data with PHP and modern browser features.<br></p>
        </hgroup>
    </header>

    <section>
        <h2>Demo</h2>
        <p class="hint">
            <strong>There is NO visual difference between obfuscated and fully revealed state.</strong>
            To see the effect, you must inspect on of the two links in the following paragraph,
            reload the page and then move your mouse or press any key.
        </p>
        <?= $contactHtml ?>
    </section>

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
