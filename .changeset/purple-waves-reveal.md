---
"html-obfuscator": patch
---

Add `->withRevealStrategy(RevealStrategy $revealStrategy)` to control when obfuscated content is revealed. Three strategies: `OnLoad` (default), `OnInteraction` (requires user interaction first), `None` (manual reveal by the developer).
