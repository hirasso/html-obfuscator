# ADR-0002: ObfuscatorConfig holds intentional global state

**Status:** Superseded (1.0.0)

`ObfuscatorConfig` was removed. The tag name is fixed (`HTMLObfuscator::TAG_NAME`, generated into the client script), so PHP and JS can't disagree on it. Client script injection was removed in favor of `clientScript()`, so there is no once-per-process state left (#53).
