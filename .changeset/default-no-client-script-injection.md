---
"html-obfuscator": major
---

`obfuscate()` no longer auto-injects the client `<script>`. Render `clientScript()` once yourself (e.g. in `<head>`), or opt in via `->injectClientScript()`. Removes the once-per-process injection guard.
