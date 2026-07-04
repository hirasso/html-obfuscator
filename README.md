# HTML Obfuscator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hirasso/html-obfuscator.svg?color=3ef09d)](https://packagist.org/packages/hirasso/html-obfuscator)
[![Test Status](https://img.shields.io/github/actions/workflow/status/hirasso/html-obfuscator/ci.yml?label=tests&color=3ef09d)](https://github.com/hirasso/html-obfuscator/actions/workflows/ci.yml)
[![Code Coverage (whatever that entails)](https://img.shields.io/codecov/c/github/hirasso/html-obfuscator?color=3ef09d)](https://app.codecov.io/gh/hirasso/html-obfuscator)

**Make crawlers earn it. Obfuscates emails, phone numbers, and other sensitive data with PHP and modern browser features.**

## Motivation

Contrary to popular belief, [this article by Spencer Mortensen](https://spencermortensen.com/articles/email-obfuscation/) shows that even moderate obfuscation dramatically reduces email harvesting by spam bots. Most bots simply scan raw HTML and don't simulate user interaction.

## How it works

On the server, PHP searches emails and phone numbers in the HTML (plain text or `href` attributes) using `regex`, obfuscates them using a randomly selected strategy, removes the original and injects a [custom element](https://developer.mozilla.org/en-US/docs/Web/API/Web_components/Using_custom_elements) in its place.

### Text nodes

Matching parts get replaced with an obfuscated element and instructions how to reveal it:

```html
<!-- before: -->
<p>Call us at +49 176 123 45 678.</p>

<!-- after: -->
<p>Call us at  <ob-fus-ca-ted value="..." aria-label="Interact with the page to reveal"><noscript>Please activate JavaScript</noscript></ob-fus-ca-ted>.</p>
```

### Links with matching `href` attribute

Everything but the scheme is stripped from the `href` attribute. The link gets a hidden obfuscated element with `[attr="href"]` injected as a child:

```html
<!-- before: -->
<a href="mailto:mail@example.com">
  Write us an email
</a>

<!-- after: -->
<a href="mailto:">
  <ob-fus-ca-ted attr="href" value="..." style="display: none;"></ob-fus-ca-ted>
  Write us an email
</a>
```

> [!NOTE]
> The scheme in obfuscated `href` attributes is preserved to prevent a [FOUC](https://en.wikipedia.org/wiki/Flash_of_unstyled_content) if links are styled using `a[href^="mailto:"]` or `a[href^="tel:"]`

### What can JS-_disabled_ crawlers see?

Instead of the original values, there are now obfuscated custom elements. One for each obfuscated `href` attribute, one for each plaintext value.

### What can JS-_enabled_ crawlers see?

Not much more, before interaction (<code>pointermove</code>, <code>pointerdown</code>, or <code>keydown</code>) was detected.

Custom elements representing a text node _do_ decode the value immediately on <code>connectedCallback</code>, but render it into a <strong>closed</strong> shadow root that is completely visible to humans but **[cannot be accessed from JavaScript](https://developer.mozilla.org/en-US/docs/Web/API/Element/attachShadow#closed)**.

<code>href</code> attributes of obfuscated links also stay empty until interaction.

## Main Features

- There is **no visual difference** between obfuscated and de-obfuscated content in the browser
- Works automatically with dynamically loaded content (AJAX/fetch, [swup](https://swup.js.org/), [htmx](https://htmx.org/), [Unpoly](https://unpoly.com/), ...)

## Other Features

- Works without configuration, but can be customized using a fluent [API](#api)
- Fully compatible with HTML5 (thanks to PHP 8.4's new `\Dom\HTMLDocument` and friends)
- Doesn't interfere with accessibility
- Extensively tested with [unit](tests/Unit), [integration](tests/Integration) and [e2e](tests/e2e) tests

## Installation

```shell
# requires PHP >= 8.4
composer require hirasso/html-obfuscator
```

## Minimal Example

Obfuscate emails and phone numbers in `$html` and automatically inject the client script that reveals `<ob-fus-ca-ted>` custom elements in the frontend:

```php
use function Hirasso\HTMLObfuscator\obfuscate;

echo obfuscate($html);
```

## Manually load the client script

By default, the client `<script>` is auto-injected into the document. If you want more control (e.g. want the script in the `<head>`), use `clientScript()` and echo it yourself:

```php
use function Hirasso\HTMLObfuscator\obfuscate;
use function Hirasso\HTMLObfuscator\clientScript;

// 1. Render the script in your <head>
echo clientScript();

// 2. Obfuscate your HTML — script injection is skipped because it was already rendered
echo obfuscate($html);
```

## API

### `->emails(bool)`

Keep emails unobfuscated

```php
echo obfuscate($html)->emails(false);
```

### `->phoneNumbers(bool)`

Keep phone numbers unobfuscated

```php
echo obfuscate($html)->phoneNumbers(false);
```

### `->debug(bool)`

Inject the client script unminified and with logging

```php
echo obfuscate($html)->debug(true);
```

### `->withAriaLabel(?string)`

Customize or disable the `aria-label` on each obfuscated element. Pass `null` to omit it entirely:

```php
echo obfuscate($html)->withAriaLabel('Hidden contact info');
echo obfuscate($html)->withAriaLabel(null); // disable
```

### `->withNoscriptText(?string)`

Customize or disable the `<noscript>` fallback inside each obfuscated element. Pass `null` to omit it:

```php
echo obfuscate($html)->withNoscriptText('Please activate JavaScript');
echo obfuscate($html)->withNoscriptText(null); // disable
```

### `->withTagName(string)`

Customize the tag name of the custom element

```php
echo obfuscate($html)->withTagName('reveal-me');
```

### `->addRegex(string)`

Add custom patterns to obfuscate text that the built-in patterns can't reach. A common case is an email address split across HTML elements to allow for a line break — the built-in email regex matches a single text node, so `<span>verylongemailaddress@</span>example.com` would slip through. You can target this specifically:

```php
echo obfuscate($html)
    ->addRegex('/[^\s@]+@/') // obfuscate the <span> text node ("verylongemailaddress@")
    ->addRegex('/[^\s.]+(\.[^\s.]+)*\.[^\s.]{2,}/') // obfuscate the domain part ("example.com")
;
```

The pattern must be a valid PCRE regex with delimiters. Each call to `->addRegex()` appends one pattern; you can chain as many as you need. An `\InvalidArgumentException` is thrown for invalid patterns.

### `[obfuscate-text]`

Add an `obfuscate-text` attribute to any element to obfuscate all of its text content — no pattern matching needed. This is a simpler alternative to `->addRegex()` when you control the markup:

```html
<span obfuscate-text><span>verylongemailaddress@</span>example.com</span>
```

Every text node inside is obfuscated wholesale, and the attribute is removed from the output:

```html
<span>
  <span><ob-fus-ca-ted value="..."></ob-fus-ca-ted></span>
  <ob-fus-ca-ted value="..."></ob-fus-ca-ted>
</span>
```

The `<ob-fus-ca-ted>` elements handle deobfuscation as usual. Content inside `<pre>`, `<code>`, `<script>`, and other excluded elements is left untouched even when nested inside an `[obfuscate-text]` element.

## Obfuscating a `HTMLDocument`

When passing a `\Dom\HTMLDocument`, the obfuscation is applied directly to the document:

```php
use Dom\HTMLDocument;
use function Hirasso\HTMLObfuscator\obfuscate;

$doc = HTMLDocument::createFromString($html);
obfuscate($doc)->saveDocument();
// $doc is now obfuscated in place
```

&rarr; Browse the <a href="./tests">tests folder</a> for more usage examples.
