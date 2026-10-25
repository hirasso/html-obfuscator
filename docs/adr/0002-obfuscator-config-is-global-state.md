# ADR-0002: ObfuscatorConfig holds intentional global state

**Status:** Accepted

## Context

`ObfuscatorConfig` exposes a static property shared across all `HTMLObfuscator` instances within a PHP process. This looks like an accidental global state smell and a candidate for per-instance config.

## Decision

Keep `ObfuscatorConfig` as global (static) state.

## Reasoning

- **`$tagName`** — the custom element name must be consistent across the entire page. PHP generates `<ob-fus-ca-ted>` elements; the client-side JS registers exactly one `customElements.define(tagName, ...)`. If two `HTMLObfuscator` instances in the same request used different tag names, only one set of elements would be decoded.

Making it per-instance would break this invariant. The test friction (`afterEach(ObfuscatorConfig::reset())`) is the honest cost of globally-scoped behavior, not a sign of bad architecture.

## Amendment (1.0.0)

`$hasInjectedClientScript` (once-per-process injection guard) was removed. Client script injection is opt-in (`->injectClientScript()`); the caller owns placing it exactly once. Implicit, order-dependent injection leaked `<script>` into the first obfuscated value in CLI/AJAX contexts (#53).
