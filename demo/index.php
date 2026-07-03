<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use function Hirasso\HTMLObfuscator\obfuscate;
use function Hirasso\HTMLObfuscator\clientScript;

$contactHtml = <<<HTML
<p>You can reach me by email at <a href="mailto:mail@rassohilber.com">mail@rassohilber.com</a> or by phone at <a href="tel:+4917620020805">+49 176 200 20 805</a>.</p>
HTML;

$script = clientScript('demo');

$rawObfuscated = (string) obfuscate($contactHtml, key: 'demo')
    ->injectClientScript(false);

$rawObfuscated = htmlspecialchars($rawObfuscated);

$currentYear = date('Y');

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HTML Obfuscator</title>
  {$script}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prism-themes@1/themes/prism-dracula.css">
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

        <h1>HTML Obfuscator</h1>
        <p><strong>Tl;dr</strong>: Make crawlers earn it. Obfuscates emails, phone numbers, and other sensitive data with PHP and modern browser features.<br></p>
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
            <summary>What can JS crawlers see?</summary>
            <p>
                Not much more, if they don't interact with the page. The custom element does decode the value on <code>connectedCallback</code>,
                but renders it into a <strong>closed</strong> shadow root that <a href="https://developer.mozilla.org/en-US/docs/Web/API/Element/attachShadow#closed">cannot be accessed from JavaScript</a>.
                The <code>href</code> attribute also stays empty until interaction
                (<code>pointermove</code>, <code>pointerdown</code>, or <code>keydown</code>) was detected.
            </p>
        </details>

    </section>

    <section>
        <h2>Motivation</h2>
        <p>
            Contrary to popular belief, <a href="https://spencermortensen.com/articles/email-obfuscation/">This article by Spencer Mortensen</a>
            shows that even moderate obfuscation dramatically reduces email harvesting by spam bots. Most bots simply scan raw HTML and don't simulate user interaction.
        </p>
    </section>

    <section>
        <h2>How it works</h2>
        <p>
            On the server, PHP finds emails and phone numbers in your HTML, XOR-encodes them with a hashed key,
            and replaces each match with a custom element:
        </p>

        <pre><code class="language-php">use function Hirasso\HTMLObfuscator\obfuscate;

echo obfuscate(\$html, key: 'my-unique-but-stable-key');</code></pre>

        <p>Each email or phone number in text nodes becomes:</p>
        <pre><code class="language-html">&lt;ob-fus-ca-ted value="base64..."&gt;&lt;/ob-fus-ca-ted&gt;</code></pre>
        <p>
            In the browser, a Web Component registered under that tag name decodes the value on
            <code>connectedCallback</code> and renders it into a closed shadow root, invisible to headless crawlers
            <strong>but completely accessible to normal users</strong>.
            After the first interaction, it replaces itself with the decoded content in the live DOM.
            The deobfuscation script is injected automatically and removes itself from the DOM after execution.
        </p>
        <p>Each <code>a[href^="mailto:"]</code> or <code>a[href^="tel:"]</code> gets a <code>ob-fus-ca-ted[attr="href"]</code> injected:</p>
        <pre><code class="language-html">&lt;ob-fus-ca-ted value="base64..." attr="href"&gt;&lt;/ob-fus-ca-ted&gt;</code></pre>
        <p>
            In the browser, the Web Component replaces its parent element's <code>href</code> with the decoded
            value on <code>connectedCallback</code> and removes itself afterwards.
        </p>
    </section>

  </main>
  <footer class="container-fluid">
    <small>
        Motivated by <a href="https://spencermortensen.com/articles/email-obfuscation/">this article</a> by Spencer Mortensen.
        Demo page built using <a href="https://picocss.com/">Pico CSS</a> and <a href="https://prismjs.com/">Prism.js</a>.
        © $currentYear by <a href="https://github.com/hirasso">Rasso Hilber</a> 👋
    </small>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-core.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>
HTML;

echo obfuscate($html, 'demo')->debug(true);
