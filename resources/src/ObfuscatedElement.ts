import { detectGlobalInteraction, settings } from "./helpers.js";

const { revealStrategy, renderPlaceholders } = settings;

/**
 * Render an obfuscated element that can reveal itself or a parent element's attribute
 */
export class ObfuscatedElement extends HTMLElement {
  get attr() {
    return this.getAttribute("attr");
  }

  connectedCallback() {
    if (revealStrategy === "onload") {
      return this.reveal();
    }

    if (renderPlaceholders && !this.attr) {
      renderPlaceholder(this);
    }

    if (revealStrategy === "oninteraction") {
      detectGlobalInteraction().then(this.reveal);
    }
  }

  reveal = () => {
    const value = this.decode();

    if (!value) {
      this.remove();
      return;
    }

    if (revealAttribute(this, value)) {
      this.remove();
      return;
    }

    this.outerHTML = value;
  };

  decode = (() => {
    let value;
    return () => {
      return (value ??= getDecodedValue(this));
    };
  })();
}

/**
 * Get the decoded value
 */
function getDecodedValue(el: ObfuscatedElement): string | undefined {
  const value = atob(el.getAttribute("value") ?? "");
  const key = el.getAttribute("key");

  if (!value || !key) {
    el.remove();
    return undefined;
  }

  return [...value]
    .map((c, i) =>
      String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length)),
    )
    .join("");
}

/**
 * Reveal a parent element's attribute value
 */
function revealAttribute(el: ObfuscatedElement, value: string): boolean {
  const attr = el.attr;
  if (!attr) return false;

  const target = el.parentElement?.closest(`[${attr}]`);
  if (!target) return false;

  target.setAttribute(attr, value);
  return true;
}

/**
 * Render a placeholder for an obfuscated element
 */
function renderPlaceholder(el: ObfuscatedElement): void {
  const value = getDecodedValue(el);
  if (!value) return;

  const injectSpan = () => {
    const span = document.createElement("span");
    el.append(span);
    span.style.overflow = "hidden";
    span.style.display = "inline-block";
    span.style.whiteSpace = "pre";
    return span;
  };

  const span = injectSpan();
  span.style.width = "1ch";
  const { width: oneChWidth } = span.getBoundingClientRect();
  span.remove();

  const spans = [...value].map((char) => {
    const span = injectSpan();
    span.textContent = char;
    return span;
  });

  const widths = spans.map((span) => span.getBoundingClientRect().width);

  spans.forEach((span, i) => {
    span.style.width = `${widths[i] / oneChWidth}ch`;
    span.textContent = "\u00a0";
  });
}
