---
type: minor
---

- Add `rev` as a second obfuscation strategy alongside `xor`. Strategies are now applied at random per value. The value attribute format changed to be strategy-first: `xor:<base64>` and `rev:<base64>`
