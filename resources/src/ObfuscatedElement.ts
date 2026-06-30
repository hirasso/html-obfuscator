import { detectGlobalInteraction, settings } from "./helpers.js";

const { revealTrigger, renderPlaceholders } = settings;

/**
 * Render an obfuscated element that can reveal itself or a parent element's attribute
 */
export class ObfuscatedElement extends HTMLElement {
  get attr() {
    return this.getAttribute("attr");
  }

  connectedCallback() {
    if (revealTrigger === "load") {
      return this.reveal();
    }

    if (renderPlaceholders && !this.attr) {
      renderPlaceholder(this);
    }

    if (revealTrigger === "interaction") {
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

  const ctx = document.createElement("canvas").getContext("2d")!;
  ctx.font = getComputedStyle(el).font;

  const oneChWidth = ctx.measureText("0").width;

  for (const char of value) {
    const span = document.createElement("span");
    span.style.overflow = "hidden";
    span.style.display = "inline-block";
    span.style.whiteSpace = "pre";
    span.style.width = `${ctx.measureText(char).width / oneChWidth}ch`;
    span.textContent = "\u00a0";
    el.append(span);
  }
}
