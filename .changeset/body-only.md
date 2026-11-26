---
"html-obfuscator": minor
---

Only the `<body>` is obfuscated. For string input, anything around the body (doctype, `<head>`, body attributes, comments) is now preserved verbatim instead of being re-serialized, and wrappers are no longer added: `obfuscate('<body>x</body>')` returns `<body>x</body>` instead of `<html><head></head><body>x</body></html>`.
