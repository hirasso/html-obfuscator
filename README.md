# HTML Obfuscator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hirasso/html-obfuscator.svg?color=3ef09d)](https://packagist.org/packages/hirasso/html-obfuscator)
[![Test Status](https://img.shields.io/github/actions/workflow/status/hirasso/html-obfuscator/ci.yml?label=tests&color=3ef09d)](https://github.com/hirasso/html-obfuscator/actions/workflows/ci.yml)
[![Code Coverage (whatever that entails)](https://img.shields.io/codecov/c/github/hirasso/html-obfuscator?color=3ef09d)](https://app.codecov.io/gh/hirasso/html-obfuscator)

**Obfuscate email addresses and phone numbers using PHP and modern web features 👀**

## Why

You might think that obfuscation won't work against spam bots. Turns out [it does](https://spencermortensen.com/articles/email-obfuscation/) if done right!

## How it works

On the server, PHP finds emails and phone numbers in the HTML, XOR-encodes them with a key derived from a passphrase (MD5 of a shuffled version of it), base64-encodes the result, and replaces the original text with a custom HTML element:

```html
<x-obfuscated value="..." key="..."></x-obfuscated>
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

```php
use function Hirasso\HTMLObfuscator\obfuscate;

echo obfuscate($html);
```

## Maximal Example

```php
use function Hirasso\HTMLObfuscator\obfuscate;

echo obfuscate($html)
    ->phoneNumbers(false)
    ->withPassphrase('nobody will guess this!')
    ->withTagName('reveal-me')
    ->debug(true);
```

## With a `\Dom\HTMLDocument`

When passing a `\Dom\HTMLDocument`, the obfuscation is applied directly to the document:

```php
use Dom\HTMLDocument;
use function Hirasso\HTMLObfuscator\obfuscate;

$doc = HTMLDocument::createFromString($html);
obfuscate($doc)->apply();
// $doc is now obfuscated in place
```

&rarr; Browse the <a href="./tests">tests folder</a> for more usage examples.
