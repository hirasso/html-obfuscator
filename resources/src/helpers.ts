/**
 * The prefix for the logger and dispatched events
 */
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