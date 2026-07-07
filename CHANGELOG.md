# Changelog

## 0.6.0

### Minor Changes

- d9ef307: Rename `RevStrategy` strategy to `RevXorStrategy` and strengthen it with position-based XOR on top of reversal

## 0.5.1

### Patch Changes

- bd76483: Add PHPBench with baseline benchmarks for obfuscation performance
- 14af52f: Fix encoding/decoding of non-ASCII characters (umlauts, emojis, etc.)
- 18b86f9: Encode the numeric strategy index together with the obfuscated value in a single base64-encoded string
- a010713: Rename `Support` to `DomHelper`, prune to `parseHtmlFragment` and `getTextNodes`

## 0.5.0

### Minor Changes

- 822f852: Add ROT47 obfuscation strategy

## 0.4.0

### Minor Changes

- 777998a: Add `rev` as a second obfuscation strategy alongside `xor`. Strategies are now applied at random per value. The value attribute format changed to be strategy-first: `xor:<base64>` and `rev:<base64>`
- 5d129b5: Remove the required `$key` parameter from all public API functions (`obfuscate()`, `clientScript()`, and the named constructors on `HTMLObfuscator`). XOR now generates a random 16-byte key per value and inlines it into the encoded blob. The `data-key` attribute is no longer injected on the client script tag

### Patch Changes

- 5d129b5: Add `readme:generate` command: parses README.md and injects it into the demo page with Shiki (nord theme) syntax highlighting. Replace Prism.js with Shiki in the demo; relative README links are rewritten to absolute GitHub URLs
- 5d129b5: Introduce `Contracts\ObfuscatedValue` interface to support multiple obfuscation strategies. Rename `ObfuscatedValue` to `XORValue` (implements `Contracts\ObfuscatedValue`)

## 0.3.0

### Minor Changes

- cd28bbd: Replace the `<obfuscate-text>` custom element with an `[obfuscate-text]` attribute

  **Breaking:** update your markup from `<obfuscate-text>...</obfuscate-text>` to `<span obfuscate-text>...</span>` (or any element).

  The attribute is removed from the output; no `display:contents` workaround is needed.

## 0.2.0

### Minor Changes

- e16d4b1: Preserve the scheme in obfuscated `href` attributes to prevent [FOUC](https://en.wikipedia.org/wiki/Flash_of_unstyled_content) if links are styled using `a[href^="mailto:"]` or `a[href^="tel:"]`
- e16d4b1: Rename `->render()` to `->saveHTML()` and `->getDocument()` to `->saveDocument()`. Apply changes in `->saveDocument()`.

### Patch Changes

- e16d4b1: Do not render aria-label and noscript for obfuscated attribute elements

## 0.1.0

### Minor Changes

- e88ed9a: Add `clientScript()` helper function to render the client script deliberatly
- 9dbab5c: Add `obfuscate()` helper function
- 81fed26: Add `<obfuscate-text>` custom element to obfuscate all textContent within
- a16ed7a: Use a closed `shadowRoot` to render obfuscated plaintext values. Cannot be read by external JavaScript.
- d6b36e5: Add dimension-optimized placeholders for obfuscated plain text

### Patch Changes

- dcdd27f: Add `->withAriaLabel()` and `->withNoscriptText()` to independently control the aria-label and noscript fallback on obfuscated elements
- 4680ea0: Add `demo/` pages, PHP e2e fixture, and Playwright tests for all three reveal triggers; rename `composer example` to `composer demo`
- 53977fb: Deploy demo to [html-obfuscator.rassohilber.com](https://html-obfuscator.rassohilber.com) via FTP on release
- abedbdc: Rename `withCustomElementName()` to `withTagName()`
- b04de68: New API method `addRegex(string $pattern)`
- 23784cb: Exclude `pre` and `code` elements from text node obfuscation
- eb45b21: Remove the client script tag from the DOM after execution
- 61df875: New method `->debug(bool)` to activate debug mode. De-obfuscates the injected frontend script and activates debug logging for it.
- 26c7e73: Inject a minified and mangled version of the frontend deobfuscation script by default
- f1475a0: Do not randomize the key anymore... require a key on initialization, instead

## 0.0.2

### Patch Changes

- 9b2aafc: fix: ignore text nodes within the `<head>` element
- 2bd1409: Minor API improvements
- 0fa29c5: Rename `<html-obfuscator-obfuscated>` to `<x-obfuscated>`. Add `->withCustomElementName()` to customize the element name.
- 5710dc6: Improve test coverage

## 0.0.1

Obfuscate emails and phone numbers in HTML using modern web technology 👀
