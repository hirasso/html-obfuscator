# HTML Obfuscator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hirasso/html-obfuscator.svg?color=3ef09d)](https://packagist.org/packages/hirasso/html-obfuscator)
[![Test Status](https://img.shields.io/github/actions/workflow/status/hirasso/html-obfuscator/ci.yml?label=tests&color=3ef09d)](https://github.com/hirasso/html-obfuscator/actions/workflows/ci.yml)
[![Code Coverage (whatever that entails)](https://img.shields.io/codecov/c/github/hirasso/html-obfuscator?color=3ef09d)](https://app.codecov.io/gh/hirasso/html-obfuscator)

**Obfuscate email addresses and phone numbers using PHP and modern web features 👀**

## Why

You might think that obfuscation won't work against spam bots. Turns out [it does](https://spencermortensen.com/articles/email-obfuscation/) if done right!

## How it works

On the server, PHP finds emails and phone numbers in the HTML, XOR-encodes them with a key (MD5 of the provided key), base64-encodes the result, and replaces the original text with a custom HTML element:

```html
<ob-fus-ca-ted value="..."></ob-fus-ca-ted>
```

In the browser, a Web Component registered under that tag name picks up each element on `connectedCallback`, reverses the XOR encoding, and swaps itself out with the decoded content. Spam bots crawling the raw HTML never see the actual email or phone number.

## Features

- Fluent API
- Fully compatible with HTML5
- All mutations are lazily queued and processed in one go
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

## `->emails(bool)`

Keep emails unobfuscated

```php
echo obfuscate($html, key: 'unique but stable key')->emails(false);
```

## `->phoneNumbers(bool)`

Keep phone numbers unobfuscated

```php
echo obfuscate($html, key: 'unique but stable key')->phoneNumbers(false);
```

## `->debug(bool)`

Inject the client script unminified and with logging

```php
echo obfuscate($html, key: 'unique but stable key')->debug(true);
```

## `->withTagName(string)`

Customize the tag name of the custom element

```php
echo obfuscate($html, key: 'unique but stable key')->withTagName('reveal-me');
```

## `->addRegex(string)`

Add custom patterns to obfuscate text that the built-in patterns can't reach. A common case is an email address split across HTML elements to allow for a line break — the built-in email regex matches a single text node, so `<span>verylongemailaddress@</span>example.com` would slip through. You can target this specifically:

```php
echo obfuscate($html, key: 'unique but stable key')
    ->addRegex('/[^\s@]+@/') // obfuscate the <span> text node ("verylongemailaddress@")
    ->addRegex('/[^\s.]+(\.[^\s.]+)*\.[^\s.]{2,}/') // obfuscate the domain part ("example.com")
;
```

The pattern must be a valid PCRE regex with delimiters. Each call to `->addRegex()` appends one pattern; you can chain as many as you need. An `\InvalidArgumentException` is thrown for invalid patterns.

## `<obfuscate-text>`

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

## With a `HTMLDocument`

When passing a `\Dom\HTMLDocument`, the obfuscation is applied directly to the document:

```php
use Dom\HTMLDocument;
use function Hirasso\HTMLObfuscator\obfuscate;

$doc = HTMLDocument::createFromString($html);
obfuscate($doc, key: 'unique but stable key')->apply();
// $doc is now obfuscated in place
```

&rarr; Browse the <a href="./tests">tests folder</a> for more usage examples.
