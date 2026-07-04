import type { ObfuscatedElement } from "./ObfuscatedElement.js";
import type { Logger } from "./helpers.js";

/** Map strategy names to their decoder functions */
const decoders = {
  xor: (data: string, _params: string[]) => decodeXOR(data),
  rev: (data: string, _params: string[]) => decodeRev(data),
  rot47: (data: string, _params: string[]) => decodeROT47(data),
};

/**
 * Decode a value
 */
export const decode = (() => {
  const cache = new Map<string, string>();

  return (el: ObfuscatedElement, logger?: Logger): string | undefined => {
    const raw = el.getAttribute("value");
    if (!raw) return;

    const [strategy, data, ...params] = raw.split(":");

    if (!strategy || !data) return undefined;

    const decoder = decoders[strategy as keyof typeof decoders];
    if (!decoder) {
      logger?.warn(`Unknown strategy: ${strategy}`);
      return undefined;
    }

    if (cache.has(raw)) {
      logger?.log(`Cache hit for ${raw}`);
      return cache.get(raw);
    }

    const decoded = decoder(data, params);
    if (!decoded) return undefined;

    cache.set(raw, decoded);

    return decoded;
  };
})();


/**
 * Decode a base64 blob where the first 16 bytes are
 * the key and the rest is the XOR ciphertext
 */
function decodeXOR(data: string): string | undefined {
  const decoded = atob(data);
  if (!decoded) return undefined;

  const key = decoded.slice(0, 16);
  const ciphertext = decoded.slice(16);

  return [...ciphertext]
    .map((c, i) =>
      String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length)),
    )
    .join("");
}

/** Decode a base64-encoded reversed string */
function decodeRev(data: string): string | undefined {
  const encoded = atob(data);
  if (!encoded) return undefined;

  return [...encoded].reverse().join("");
}

/** Decode a base64-encoded ROT47 string */
function decodeROT47(data: string): string | undefined {
  const decoded = atob(data);
  if (!decoded) return undefined;
  return [...decoded]
    .map((c) => {
      const n = c.charCodeAt(0);
      return n >= 33 && n <= 126
        ? String.fromCharCode(33 + ((n - 33 + 47) % 94))
        : c;
    })
    .join("");
}
