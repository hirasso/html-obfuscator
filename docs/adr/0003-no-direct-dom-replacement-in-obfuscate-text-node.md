# ADR-0003: Keep DOM→string→DOM round-trip in obfuscateTextNode

**Status:** Accepted

## Context

`obfuscateTextNode` uses `preg_replace_callback` to find matches in a text node's string data, serializes each match to an HTML string (via a scratch `HTMLDocument::createEmpty()` + `importNode` + `saveHTML()`), then re-parses the resulting string back to a DOM fragment via `DomHelper::parseHtmlFragment`. This round-trip was identified as a candidate for replacement with direct DOM manipulation (`preg_match_all` + `splitText` + `insertBefore`), motivated by performance concerns.

## Decision

Keep the current DOM→string→DOM round-trip. Do not replace it with direct DOM manipulation.

## Reasoning

Benchmarks show the full obfuscation pass (including all round-trips) costs:

- **~87 µs** for a sparse page (~5 obfuscatable items)
- **~245 µs** for a dense page (~20 obfuscatable items)

Both are well under 1ms. In a typical PHP request budget of 50–200ms, this is 0.1–0.5% of total time. The scratch `HTMLDocument::createEmpty()` allocations per match are not a meaningful cost at realistic page sizes.

The direct DOM alternative (`splitText` + `insertBefore`) would be more code, harder to read, and trades a working tested implementation for negligible gain.

If bulk processing of thousands of documents becomes a use case, revisit with fresh benchmarks at that scale.
