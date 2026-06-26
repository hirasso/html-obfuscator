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
 * Detect interaction
 */
export const detectInteraction = (() => {
  let hasInteracted = false;

  return (
    target: HTMLElement,
    events: (keyof DocumentEventMap)[] | undefined = undefined,
  ): Promise<void> => {
    events ??= ["pointermove", "pointerdown", "keydown"];

    const abortCtrl = new AbortController();

    return new Promise<void>((resolve) => {
      if (hasInteracted) resolve();

      events.forEach((eventName) => {
        document.addEventListener(
          eventName,
          (e) => {
            abortCtrl.abort();
            hasInteracted = true;
            resolve();
          },
          abortCtrl,
        );
      });
    });
  };
})();
