/**
 * Apply styles to an element, with intellisense support
 */
export function applyStyles(
  el: HTMLElement,
  styles: Partial<CSSStyleDeclaration>,
): void {
  Object.assign(el.style, styles);
}

/**
 * Prefix a string with our prefix
 */
export function prefixed(str: string): string {
  return `html-obfuscator:${str}`;
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
 * Detect interaction. Dedupes and short-circuits
 * repeated calls against the same element.
 */
export const detectInteraction = (() => {
  let hasInteracted = false;
  const promises = new Map<HTMLElement, Promise<HTMLElement>>();

  return <T extends HTMLElement>(
    target: T,
    events: (keyof DocumentEventMap)[] | undefined = undefined,
  ): Promise<T> => {
    events ??= ["pointermove", "pointerdown", "keydown"];

    if (hasInteracted) return Promise.resolve(target);

    if (!promises.has(target)) {
      promises.set(target, new Promise<T>((resolve) => {
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
      }));
    }

    return promises.get(target)! as Promise<T>;
  };
})();
