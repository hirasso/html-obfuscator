import { ObfuscatedElement } from "./ObfuscatedElement.js";

const prefix = "html-obfuscator";

/**
 * Create a minimal logger with a prefix, if settings.debug = true
 */
export type Logger = ReturnType<typeof createLogger>;
export function createLogger() {
  const style = [
    "background: linear-gradient(to right, #a960ee, #f78ed4)",
    "color: white",
    "padding-inline: 4px",
    "border-radius: 2px",
    "font-family: monospace",
  ].join(";");

  return {
    log: (...args: any[]) => console.log(`%c${prefix}`, style, ...args),
    warn: (...args: any[]) => console.warn(`%c${prefix}`, style, ...args),
    error: (...args: any[]) => console.error(`%c${prefix}`, style, ...args),
  };
}

/**
 * Prefix a string with our prefix
 */
export function prefixed(str: string): string {
  return `${prefix}:${str}`;
}

/**
 * Dispatch custom prefixed events
 */
export function dispatch(eventName: string): void {
  document.documentElement.dispatchEvent(
    new CustomEvent(prefixed(eventName), { bubbles: true }),
  );
}

/**
 * Detect interaction anywhere on the window
 */
export function detectGlobalInteraction() {
  return detectInteraction(window);
}

/**
 * Detect interaction. Dedupes and short-circuits
 * repeated calls against the same element.
 */
export const detectInteraction = (() => {
  let hasInteracted = false;
  const promises = new Map<EventTarget, Promise<EventTarget>>();

  return <T extends HTMLElement | Document | Window>(
    target: T,
    events: (keyof DocumentEventMap)[] = [
      "pointermove",
      "pointerdown",
      "keydown",
    ],
  ): Promise<T> => {
    if (hasInteracted) return Promise.resolve(target);

    if (!promises.has(target)) {
      promises.set(
        target,
        new Promise<T>((resolve) => {
          const abortCtrl = new AbortController();

          events.forEach((eventName) => {
            target.addEventListener(
              eventName,
              () => {
                abortCtrl.abort();
                hasInteracted = true;
                resolve(target);
              },
              { signal: abortCtrl.signal },
            );
          });
        }),
      );
    }

    return promises.get(target)! as Promise<T>;
  };
})();

/** Decode a base64 blob where the first 16 bytes are the key and the rest is the XOR ciphertext */
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

/** Map strategy names to their decoder functions */
const decoders = {
  xor: (data: string, _params: string[]) => decodeXOR(data),
  rev: (data: string, _params: string[]) => decodeRev(data),
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
