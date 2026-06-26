import { ObfuscatedElement } from "./ObfuscatedElement.js";

const prefix = "html-obfuscator";

/**
 * Load data from an `application/json` script tag
 */
type ScriptSettings = Hirasso.HTMLObfuscator.ScriptSettings;
const defaults: ScriptSettings = {
  tagName: "x-obfuscated",
  debug: false,
  revealStrategy: "onload",
  renderPlaceholders: false,
};

/**
 * Load the settings from the script tag
 */
export const settings: ScriptSettings = (() => {
  const attr = document.currentScript?.getAttribute("data-settings");
  if (!attr) return defaults;

  try {
    return JSON.parse(attr);
  } catch (e) {
    return defaults;
  }
})();

/**
 * Get a minimal logger with a prefix, if settings.debug = true
 */
export const logger = (() => {
  if (!settings.debug) {
    return null;
  }

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
})();

/**
 * Load the data from a json script tag
 */
export const loadSettingsFromJsonScriptTag = (() => {
  const store = new Map<string, any>();

  return function <T>(selector: string): T {
    if (store.has(selector)) {
      return store.get(selector);
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

    store.set(selector, value.settings);

    return value.settings as T;
  };
})();

/**
 * Get an attribute, validate it
 */
function attr<T extends string>(
  el: HTMLElement,
  attribute: string,
  allowedValues: readonly T[],
): T | null {
  const value = el.getAttribute(attribute);
  if (value !== null && (allowedValues as readonly string[]).includes(value)) {
    return value as T;
  }
  return null;
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
 * Inject styles into the head
 */
function injectStyles(styles: string) {
  const el = document.createElement("style");
  el.textContent = styles;
  document.head.append(el);
  return el;
}

/**
 * Render a placeholder for the obfuscated element
 */
export const renderPlaceholder = (() => {
  let injectedStyles = false;

  const styles = /* css */ `
    :where(x-obfuscated) {
      display: inline-flex;
      cursor: pointer
    }
    :where(x-obfuscated > span) {
      width: 0.8ch;
      height: 1.3cap;
      overflow: hidden;
      border: 1px solid;
      background: black;
    }
  `;

  return (el: ObfuscatedElement) => {
    if (!injectedStyles) {
      injectStyles(styles);
    }
    injectedStyles = true;

    const charCount = parseInt(el.getAttribute("char-count") ?? "0", 10);
    const span = document.createElement("span");
    span.textContent = "e";
    for (let i = 0; i < charCount; i++) {
      const clone = span.cloneNode(true);
      el.append(clone);
    }
  };
})();
