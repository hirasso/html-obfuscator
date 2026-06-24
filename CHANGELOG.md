# Changelog

## 0.0.1

### Patch Changes

- 9345217: Obfuscate emails and phone numbers in HTML using modern web technology 👀

## 2.1.3

### Patch Changes

- f449036: Make API arguments more strict
- d01097c: Simplify internals of `parseHtml()`

## 2.1.2

### Patch Changes

- 49cf5b8: Use more `Dom\Document`-native functionality
- 99c35e9: Optimize `getTextNodes` to return only non-empty nodes
- 382ed5c: Make parsing of text nodes more robust

## 2.1.1

### Patch Changes

- c2aa7e8: Fix: Do not link already-linked urls again

## 2.1.0

### Minor Changes

- 3f4dd11: Add `->when()` for conditional fluent chaining

## 2.0.2

### Patch Changes

- a732034: Normalize `scheme` and `host` in URIs
- d7f2de4: Fix: do not encode quotes around href attributes during obfuscation

## 2.0.1

### Patch Changes

- 3bc52d1: Fix PrefixLinker incorrectly re-linking text inside `<a>` elements; bump test coverage to 100%
- 8f2b7af: Remove `removeDuplicateIds` — no longer needed with `Dom\HTMLDocument`
- 17261ad: Remove `domQueueEarly`; `autolinkUrls` now runs in the same DOM pass as other processors
- b9361b2: Loosen composer requirements
- 5313d63: Drop the `league/uri` dependency

## 2.0.0

### Major Changes

- cb6fa30: Renamed `->obfuscateEmails()` to `->obfuscateContacts(email: true, phone: true)`
- cb6fa30: obfuscate telepone numbers, as well

### Patch Changes

- 8a92584: Add `->dump()` and `->dd()` methods for debugging

## 1.0.0

### Major Changes

- 95c2252: Rename `->encodeEmails` to `->confuseBots`
- 0bc5ffc: > From now on, php `>=8.4` is the minimum requirement. This allows us to use the new native `DOM\Document` class, which saves a LOT of trouble.
- 9fe3e02: > Removed all typography helpers. These can be better handled by CSS nowadays.

### Minor Changes

- 54e9f40: New fluid method: `->stripTags()`

## 0.7.1

### Patch Changes

- ac71233: Ignore inch notation when normalizing quotes (e.g. '50"')
- 3b74fcc: Fix quote boundary detection to allow punctuation (e.g. parentheses) around quotes

## 0.7.0

### Minor Changes

- 4c3a120: Add new typography service `normalizeQuotes`

  ```php
  use function Hirasso\HTMLObfuscator\process;
  echo process($html)->typography('en', fn ($typo) => $typo->normalizeQuotes());
  ```

## 0.6.3

### Patch Changes

- ac17b59: Rename `->preventWidows` to `->avoidShortLastLines`, according to the [decision of the CSS working group](https://github.com/w3c/csswg-drafts/issues/11283#issuecomment-2776875912)
- 5ce193d: Make it easier to contribute new quote styles for `QuoteLocalizer`
- b4a9818: Optimize the internal structure of `avoidShortLastLine`

## 0.6.2

### Patch Changes

- 0ef90f4: Implement stack-based quote localization for nested quotes
- 0ef90f4: Add PHPBench benchmarks with CI regression testing

## 0.6.1

### Patch Changes

- a53ea9a: Robuster structure for `QuoteLocalizer`
- b6b5070: Make opening external links in a new tab safe by default by adding [rel="noopener noreferrer"]

## 0.6.0

### Minor Changes

- 4bf9b49: Simplify the API for `processLinks`

  ```php
  use function Hirasso\HTMLObfuscator\process;

  echo process($html)->processLinks(fn ($link) => $link->addClasses()->openExternalInNewTab());
  ```

## 0.5.7

### Patch Changes

- f2fd7c0: Do not autolink strings without scheme (e.g. "Architekt.innen")

## 0.5.6

### Patch Changes

- 0806776: Do not wrongly replace single quotes in a string like this: `Edith's Lädchen's Öffnungszeiten`

## 0.5.5

### Patch Changes

- 26924e8: export-ignore some files
- 769010d: Switch License to MIT

## 0.5.4

### Patch Changes

- 51bae60: Rename `UrlType` to `LinkType`

## 0.5.3

### Patch Changes

- 8c5a895: Fix timing and encoding issues

## 0.5.2

### Patch Changes

- de4dc40: Fix phpstan errors
- 5b5af67: Use native `htmlentities` with `$double_encode` set to false to prevent double-encoding

## 0.5.1

### Patch Changes

- 2a683fe: don't require illuminate/collections

## 0.5.0

### Minor Changes

- 34cb0a0: Rename the helper function `html()` to `process()`:

  ```php
  use function Hirasso\HTMLObfuscator\process;

  echo process($html)
      ->autolinkUrls() // wrap raw url strings in `<a>` tags
      ->removeEmptyElements('p') // remove empty paragraphs
      ->encodeEmails() // encode emails to confuse spam bots
      ->typography('de_DE') // fix typography based on locale
      ->processLinks(); // add classes based on link type
  ```

### Patch Changes

- a8f6236: Rename enum `LinkType` to `UrlType`

## 0.4.2

### Patch Changes

- 2a4ce16: Fixes links being considered external if they contained a custom port number

## 0.4.1

### Patch Changes

- 29c68b7: Provide a helper function:

  ```php
  use function Hirasso\HTMLObfuscator\process;

  echo process($html)->autolinkUrls();
  ```

## 0.4.0

### Minor Changes

- 6564cae: Change the API for `->typography()`:

  ```php
  use Hirasso\HTMLObfuscator\HTMLObfuscator;

  echo HTMLProcessor::fromString($html)
      ->typography('de_DE', fn ($typo) => $typo
          ->localizeQuotes() // format quotes based on locale
          ->preventWidows() // prevent widows
      );
  ```

## 0.3.0

### Minor Changes

- c594de9: Make the API for processing links more flexible and intuitive:

  ```php
  use Hirasso\HTMLObfuscator\HTMLObfuscator;

  echo HTMLProcessor::fromString($html)
      ->processLinks(function ($link, $defaultHandler) {
          if ($link->type->value === 'external') {
              $link->el->setAttribute('target', '_blank');
          }
          $defaultHandler(); // run the default handler
      });
  ```

## 0.2.0

### Minor Changes

- bb7db84: Use a fluid API for registering the Typography options

  ```php
  echo HTMLProcessor::fromString($html)
      ->typography(
          Typography::make('de_DE')
              ->localizeQuotes()
              ->preventWidows()
      );
  ```

## 0.1.4

### Patch Changes

- 3d43518: Fix very specific issues with libxml in certain versions

## 0.1.3

### Patch Changes

- a45f63a: Only allow duplicate IDs with a LIBXML_VERSION lower then 21000.

  Otherwise, HTML might not be parsed correctly (observed with
  tags and multiple lines)

## 0.1.2

### Patch Changes

- 513ad82: Fix widow prevention for text containing HTML entities (umlauts). The length check now correctly counts decoded characters instead of entity byte length, preventing false positives that skipped widow prevention on German text.

## 0.1.1

### Patch Changes

- 46d54e6: Only prevent widows in the last text node of each block element

## 0.1.0

### Minor Changes

- 14851a3: Rework the API:

  ```php
  echo HTMLProcessor::fromString($html)
      ->autolinkUrls() // wrap raw url strings in `<a>` tags
      ->autolinkPrefix('@', 'https://your-instance.social/@') // link @profileName to Mastodon
      ->autolinkPrefix('#', 'https://your-instance.social/tags') // link #hashTag to Mastodon
      ->removeEmptyElements('p') // remove empty paragraphs
      ->encodeEmails() // encode emails to confuse spam bots
      ->typography( // optimize typography
          'de_DE', // currently supported: 'en', 'de', 'fr'
          localizeQuotes: true, // format quotes based on locale
          preventWidows: true // prevent widows
      )
      ->processLinks(function ($el, $type) { // process links by callback
              if ($type === UrlType::External) {
                  $el->setAttribute('target', '_blank');
              }
          },
          addClasses: true // automatically add classes by type (mailto:, tel, internal, external, ...)
      );
  ```

## 0.0.4

### Patch Changes

- a3c8c6e: Run assigned tasks lazily and in an order that makes sense
- 1aba0f8: Refactor code

## 0.0.3

### Patch Changes

- bf92a41: Allow controlling what should be beautified:

  ```php
  HirassoHTMLProcessor::fromString($html)
    ->beautify(
      removeEmptyParagraphs: true,
      preventWidows: true
    );
  ```

## 0.0.2

### Patch Changes

- 60faf71: Generalize social linking

## 0.0.1

### Patch Changes

- 33c8d09: Initial Release
