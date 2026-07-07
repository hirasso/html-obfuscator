# ADR-0001: No injection seam between HTMLObfuscator and Obfuscator

**Status:** Accepted

## Context

`HTMLObfuscator::__construct()` hardwires `new Obfuscator()`. There is no seam between the DOM-processing module and the encoding module. This looks like a candidate for dependency injection — inject a deterministic encoder in tests, the real `Obfuscator` in production.

## Decision

Do not add an injection seam here.

## Reasoning

- **One adapter, not two.** There is only one production adapter (`Obfuscator`). A test-only stub would be the only second adapter. Per the design principle: one adapter = hypothetical seam; two adapters = real one.

- **Deterministic strategies are already pinnable.** `RevStrategy` and `Rot47Strategy` are deterministic. Tests that need to assert on encoded values can already call `->setStrategy(RevStrategy::class)` and check the exact output without injection.

- **Round-trip correctness is tested cross-language by design.** `GenerateFixturesCommand` exports PHP-encoded fixtures to `tests/vitest/fixtures/decode.json`. `decode.test.ts` verifies those fixtures decode correctly in JS. This is the deliberate answer to round-trip testing — not a PHP-side decoder or a stub encoder.

- **DOM processing and encoding don't need isolation.** The structural tests (element presence, attribute presence, element count) work fine against the real `Obfuscator`. There is no test that the current interface blocks.
