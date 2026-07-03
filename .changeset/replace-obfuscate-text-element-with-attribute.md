---
"html-obfuscator": minor
---

Replace the `<obfuscate-text>` custom element with an `[obfuscate-text]` attribute

**Breaking:** update your markup from `<obfuscate-text>...</obfuscate-text>` to `<span obfuscate-text>...</span>` (or any element).

The attribute is removed from the output; no `display:contents` workaround is needed.