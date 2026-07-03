---
"html-obfuscator": minor
---

Preserve the scheme in obfuscated `href` attributes to prevent [FOUC](https://en.wikipedia.org/wiki/Flash_of_unstyled_content) if links are styled using `a[href^="mailto:"]` or `a[href^="tel:"]`
