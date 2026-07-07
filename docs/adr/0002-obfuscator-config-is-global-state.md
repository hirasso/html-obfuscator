# ADR-0002: ObfuscatorConfig holds intentional global state

**Status:** Accepted

## Context

`ObfuscatorConfig` exposes two static properties shared across all `HTMLObfuscator` instances within a PHP process. This looks like an accidental global state smell and a candidate for per-instance config.

## Decision

Keep `ObfuscatorConfig` as global (static) state.

## Reasoning

Both properties are load-bearing at the process level:

- **`$tagName`** — the custom element name must be consistent across the entire page. PHP generates `<ob-fus-ca-ted>` elements; the client-side JS registers exactly one `customElements.define(tagName, ...)`. If two `HTMLObfuscator` instances in the same request used different tag names, only one set of elements would be decoded.

- **`$hasInjectedClientScript`** — the deobfuscation `<script>` must appear exactly once per page, even when `HTMLObfuscator` is called multiple times (e.g. header, body, widgets rendered separately). A per-instance flag would re-inject the script on every call.

Making either property per-instance would break these invariants. The test friction (`afterEach(ObfuscatorConfig::reset())`) is the honest cost of globally-scoped behavior, not a sign of bad architecture.
