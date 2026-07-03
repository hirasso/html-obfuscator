# HTML Obfuscator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hirasso/html-obfuscator.svg?color=3ef09d)](https://packagist.org/packages/hirasso/html-obfuscator)
[![Test Status](https://img.shields.io/github/actions/workflow/status/hirasso/html-obfuscator/ci.yml?label=tests&color=3ef09d)](https://github.com/hirasso/html-obfuscator/actions/workflows/ci.yml)
[![Code Coverage (whatever that entails)](https://img.shields.io/codecov/c/github/hirasso/html-obfuscator?color=3ef09d)](https://app.codecov.io/gh/hirasso/html-obfuscator)

**Make crawlers work for it. Obfuscates emails, phone numbers, and other sensitive contact data with PHP and modern browser features.**

## Why

You might think that obfuscation won't work against spam bots. [This article](https://spencermortensen.com/articles/email-obfuscation/) by Spencer Mortensen documents that even moderate obfuscation dramatically reduces harvesting. Most bots scan raw HTML and don't simulate user interaction.

## How it works

On the server, PHP finds emails and phone numbers in the HTML, XOR-encodes them with a hashed key, base64-encodes the result, and replaces the original text with a custom HTML element:

```html
<ob-fus-ca-ted value="..." aria-label="Interact with the page to reveal">
  <noscript>Please activate JavaScript</noscript>
</ob-fus-ca-ted>
```

In the browser, a Web Component registered under that tag name picks up each element on `connectedCallback`, reverses the XOR encoding and renders the result in a **closed** `shadowRoot` that cannot be read by crawlers. The content gets "unwrapped" into the light DOM only after interaction with the page was detected.

## Features

- Fluent [API](#api)
- 
- Fully compatible with HTML5
- Extensively tested

## Installation

```shell
# requires PHP >= 8.4
composer require hirasso/html-obfuscator
```

## Minimal Example

Obfuscate emails and phone numbers in `$html` and automatically injects the client script required for de-obfuscation of the resulting `<ob-fus-ca-ted>` custom elements:

```php
use function Hirasso\HTMLObfuscator\obfuscate;

/** vanilla: */
echo obfuscate($html, key: 'unique but stable key');
/** or in Laravel: */
echo obfuscate($html, key: config('app.key'));
/** or in WordPress: */
echo obfuscate($html, key: wp_salt());
/** or in Kirby: */
echo obfuscate($html, key: /** TODO */);
/** or in ProcessWire: */
echo obfuscate($html, key: $config->userAuthSalt);
```

## Rendering the client script manually

By default, the client `<script>` is auto-injected into the document. If you need it in a specific location (e.g. in the `<head>`), use `clientScript()` and echo it yourself:

```php
use function Hirasso\HTMLObfuscator\obfuscate;
use function Hirasso\HTMLObfuscator\clientScript;

// 1. Render the script in your <head>
echo clientScript(key: 'unique but stable key');

// 2. Obfuscate your HTML — script injection is skipped because it was already rendered
echo obfuscate($html, key: 'unique but stable key');
```

## API

### `->emails(bool)`

Keep emails unobfuscated

```php
echo obfuscate($html, key: 'unique but stable key')->emails(false);
```

### `->phoneNumbers(bool)`

Keep phone numbers unobfuscated

```php
echo obfuscate($html, key: 'unique but stable key')->phoneNumbers(false);
```

### `->debug(bool)`

Inject the client script unminified and with logging

```php
echo obfuscate($html, key: 'unique but stable key')->debug(true);
```

### `->withAriaLabel(?string)`

Customize or disable the `aria-label` on each obfuscated element. Pass `null` to omit it entirely:

```php
echo obfuscate($html, key: 'unique but stable key')->withAriaLabel('Hidden contact info');
echo obfuscate($html, key: 'unique but stable key')->withAriaLabel(null); // disable
```

### `->withNoscriptText(?string)`

Customize or disable the `<noscript>` fallback inside each obfuscated element. Pass `null` to omit it:

```php
echo obfuscate($html, key: 'unique but stable key')->withNoscriptText('Please activate JavaScript');
echo obfuscate($html, key: 'unique but stable key')->withNoscriptText(null); // disable
```

### `->withTagName(string)`

Customize the tag name of the custom element

```php
echo obfuscate($html, key: 'unique but stable key')->withTagName('reveal-me');
```

### `->addRegex(string)`

Add custom patterns to obfuscate text that the built-in patterns can't reach. A common case is an email address split across HTML elements to allow for a line break — the built-in email regex matches a single text node, so `<span>verylongemailaddress@</span>example.com` would slip through. You can target this specifically:

```php
echo obfuscate($html, key: 'unique but stable key')
    ->addRegex('/[^\s@]+@/') // obfuscate the <span> text node ("verylongemailaddress@")
    ->addRegex('/[^\s.]+(\.[^\s.]+)*\.[^\s.]{2,}/') // obfuscate the domain part ("example.com")
;
```

The pattern must be a valid PCRE regex with delimiters. Each call to `->addRegex()` appends one pattern; you can chain as many as you need. An `\InvalidArgumentException` is thrown for invalid patterns.

### `<obfuscate-text>`

Wrap any HTML in an `<obfuscate-text>` element to obfuscate all of its text content — no pattern matching needed. This is a simpler alternative to `->addRegex()` when you control the markup:

```html
<obfuscate-text><span>verylongemailaddress@</span>example.com</obfuscate-text>
```

Every text node inside is obfuscated wholesale:

```html
<obfuscate-text style="display:contents">
  <span><ob-fus-ca-ted value="..."></ob-fus-ca-ted></span>
  <ob-fus-ca-ted value="..."></ob-fus-ca-ted>
</obfuscate-text>
```

The element itself renders transparently (`display:contents`) and requires no JavaScript of its own — the inner `<ob-fus-ca-ted>` elements handle deobfuscation as usual. Content inside `<pre>`, `<code>`, `<script>`, and other excluded elements is left untouched even when nested inside `<obfuscate-text>`.

## Obfuscating a `HTMLDocument`

When passing a `\Dom\HTMLDocument`, the obfuscation is applied directly to the document:

```php
use Dom\HTMLDocument;
use function Hirasso\HTMLObfuscator\obfuscate;

$doc = HTMLDocument::createFromString($html);
obfuscate($doc, key: 'unique but stable key')->apply();
// $doc is now obfuscated in place
```

&rarr; Browse the <a href="./tests">tests folder</a> for more usage examples.
