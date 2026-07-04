---
"html-obfuscator": minor
---

Remove the required `$key` parameter from all public API functions (`obfuscate()`, `clientScript()`, and the named constructors on `HTMLObfuscator`). XOR now generates a random 16-byte key per value and inlines it into the encoded blob. The `data-key` attribute is no longer injected on the client script tag
