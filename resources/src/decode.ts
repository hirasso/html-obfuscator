import type { Logger } from "./helpers.js";
import { strategyOrder } from "./generated/strategies.js";

type Decoder = (data: string) => string | undefined;

const decoderMap: Record<string, Decoder> = {
  xor: decodeXOR,
  rev: decodeRev,
  rot47: decodeROT47,
};

/** Ordered by index, matching Obfusator::STRATEGIES */
const decoders: Decoder[] = strategyOrder.map((name) => decoderMap[name]);

/**
 * Decode a value
 */
export const decode = (() => {
  const cache = new Map<string, string>();

  return (value: string, logger?: Logger): string | undefined => {
    const cached = cache.get(value);

    if (cached) {
      logger?.log(`Cache hit for ${value}`);
      return cached;
    }

    const { index, data } = unwrapAttribute(atob(value)) ?? {};
    if (index == null || !data) return;

    const decoder = decoders[index];

    if (!decoder) {
      logger?.warn(`Unknown strategy index: ${index}`);
      return;
    }

    const result = decoder(data);
    if (!result) return;

    const utf8result = byteStringToUtf8(result);
    cache.set(value, utf8result);

    return utf8result;
  };
})();

/**
 * Unwrap an obfuscated attribute
 * checks if the value has the format {number}:{string}
 * if so, parses and returns both in an object
 */
function unwrapAttribute(value: string) {
  if (!value) return;

  const match = value.match(/^(?<key>\d+):(?<data>.+)/s)?.groups;
  if (!match) return;

  return {
    index: parseInt(match.key, 10),
    data: match.data,
  };
}

/**
 * Decode a blob string where the first 16 bytes are
 * the key and the rest is the XOR ciphertext
 */
function decodeXOR(value: string): string | undefined {
  const key = value.slice(0, 16);
  const ciphertext = value.slice(16);

  return [...ciphertext]
    .map((c, i) =>
      String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length)),
    )
    .join("");
}

/** Decode a reversed string */
function decodeRev(value: string): string | undefined {
  if (!value) return undefined;

  return [...value].reverse().join("");
}

/** Decode a ROT47 string */
function decodeROT47(value: string): string | undefined {
  return [...value]
    .map((c) => {
      const n = c.charCodeAt(0);
      return n >= 33 && n <= 126
        ? String.fromCharCode(33 + ((n - 33 + 47) % 94))
        : c;
    })
    .join("");
}

/** Convert a binary string (from atob) to a UTF-8 string */
function byteStringToUtf8(str: string): string {
  return new TextDecoder().decode(Uint8Array.from(str, (c) => c.charCodeAt(0)));
}
