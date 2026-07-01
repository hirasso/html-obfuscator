import { ObfuscatedElement } from "./ObfuscatedElement.js";

const prefix = "html-obfuscator";

/**
 * Create a minimal logger with a prefix, if settings.debug = true
 */
export type Logger = ReturnType<typeof createLogger>;
export const createLogger = () => {
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
};

/**
 * Load the data from a json script tag
 */
export const loadSettingsFromJsonScriptTag = (() => {
  const cache = new Map<string, any>();

  return function <T>(selector: string): T {
    if (cache.has(selector)) {
      return cache.get(selector);
    }

    const el = document.getElementById(selector);
    if (!el) {
      throw new Error(`No script data element found for "${selector}"`);
    }

    let value: any;
    try {
      value = JSON.parse(el.textContent ?? "");
    } catch {
      throw new Error(`Failed to parse script data for "${selector}"`);
    }

    if (!value.settings) {
      throw new Error(`No settings found in script data for "${selector}"`);
    }

    cache.set(selector, value.settings);

    return value.settings as T;
  };
})();

/**
 * Prefix a string with our prefix
 */
export function prefixed(str: string): string {
  return `${prefix}:${str}`;
}

/**
 * Dispatch custom prefixed events
 */
export function dispatchPrefixedEvent(eventName: string): void {
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
    events: (keyof DocumentEventMap)[] | undefined = undefined,
  ): Promise<T> => {
    events ??= ["pointermove", "pointerdown", "keydown"];

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

/**
 * Decode a value
 */
export const decode = (el: ObfuscatedElement): string | undefined => {
  const value = atob(el.getAttribute("value") ?? "");
  const key = el.getAttribute("key");

  if (!value || !key) {
    return undefined;
  }

  return [...value]
    .map((c, i) =>
      String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length)),
    )
    .join("");
};
