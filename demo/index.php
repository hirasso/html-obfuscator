<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Hirasso\HTMLObfuscator\HTMLObfuscator;

use function Hirasso\HTMLObfuscator\obfuscate;

$contactHtml = <<<HTML
<p>You can reach me by email at <a href="mailto:mail@rassohilber.com">mail@rassohilber.com</a> or by phone at <a href="tel:+4917620020805">+49 176 200 20 805</a>.</p>
HTML;

$rawObfuscated = (string) obfuscate($contactHtml)
    ->injectFrontendScript(false)
    ->randomizeKey(false);

$rawObfuscated = htmlspecialchars($rawObfuscated);

HTMLObfuscator::$hasInjectedFrontendScript = false;

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HTML Obfuscator: Require work from crawlers before revealing contact information</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prism-themes@1/themes/prism-dracula.css">
  <link rel="stylesheet" href="demo.css">
</head>
<body>
  <main class="container">

    <header>
      <h1>HTML Obfuscator</h1>
      <p>Obfuscate email addresses and phone numbers using PHP and modern web features 👀</p>
      <p><a href="https://github.com/hirasso/html-obfuscator">github.com/hirasso/html-obfuscator</a></p>
    </header>

    <section>
      <h2>Demo</h2>
      <p class="hint">Inspect one of the two links in the following paragraph, reload the page and then move your mouse or press any key to see the effect.</p>
      {$contactHtml}
    </section>

    <section>
        <details>
            <summary>What can non-JS crawlers see?</summary>
            <p>The original email and phone number are gone. Only 4 encoded custom elements remain. One for each <code>href</code> attribute, one for each plaintext value:</p>
            <pre><code class="language-html">{$rawObfuscated}</code></pre>
        </details>

        <details>
            <summary>What can JS crawlers see before interaction?</summary>
            <p>
                Not much more. The Web Component does decode the value on <code>connectedCallback</code>,
                but renders it into a <strong>closed</strong> shadow root — inaccessible from outside JavaScript (verified via e2e tests).
                The <code>href</code> attribute also stays empty until interaction
                (<code>pointermove</code>, <code>pointerdown</code>, or <code>keydown</code>) was detected.
            </p>
        </details>

    </section>

    <section>
        <h2>Does this even make sense?</h2>
        <p>
            You might think spam bots are too clever for this.
            <a href="https://spencermortensen.com/articles/email-obfuscation/">This article by Spencer Mortensen</a>
            documents that even moderate obfuscation dramatically reduces harvesting. Most bots scan raw HTML
            and don't simulate user interaction. My best guess is that it's just too expensive.
        </p>
    </section>

    <section>
        <h2>How it works</h2>
        <p>
            On the server, PHP finds emails and phone numbers in your HTML, XOR-encodes them with a key
            derived from a randomized passphrase, and replaces each match with a custom element:
        </p>

        <pre><code class="language-php">use function Hirasso\HTMLObfuscator\obfuscate;

echo obfuscate(\$html);</code></pre>

        <p>Each email or phone number in the source becomes:</p>
        <pre><code class="language-html">&lt;x-obfuscated value="base64..." key="md5..."&gt;&lt;/x-obfuscated&gt;</code></pre>
        <p>
            In the browser, a Web Component registered under that tag name decodes the value on
            <code>connectedCallback</code> and renders it into a closed shadow root. After the first
            real user interaction, it replaces itself with the decoded content in the live DOM.
            The deobfuscation script is injected automatically and removes itself from the DOM after execution.
        </p>
    </section>

  </main>
  <script src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-core.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>
HTML;

echo obfuscate($html)->randomizeKey(false);
