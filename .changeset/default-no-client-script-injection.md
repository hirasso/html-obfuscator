---
"html-obfuscator": major
---

- `obfuscate()` no longer injects the client `<script>`. Render `clientScript()` once yourself (e.g. in `<head>`).
- `clientScript(debug: true)` renders the un-minified script.
- Removed: `->injectClientScript()`, `->withTagName()` (tag is always `<ob-fus-ca-ted>`), `HTMLObfuscator::createEmpty()`, `createForClientScript()`, `ObfuscatorConfig`.
- `DEFAULT_TAG_NAME` renamed to `TAG_NAME`.
