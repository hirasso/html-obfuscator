import { detectGlobalInteraction, settings } from "./helpers.js";

const { revealStrategy, renderPlaceholders } = settings;

/**
 * Renders an obfuscated element that can reveal itself
 */
export class ObfuscatedElement extends HTMLElement {
  connectedCallback() {
    if (revealStrategy === "onload") {
      return this.reveal();
    }

    maybeRenderPlaceholder(this);

    if (revealStrategy === "oninteraction") {
      detectGlobalInteraction().then(this.reveal);
    }
  }

  reveal = () => {
    const value = getDecodedValue(this);

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
  const attr = el.getAttribute("attr");
  if (!attr) return false;

  const target = el.parentElement?.closest(`[${attr}]`);
  if (!target) return false;

  target.setAttribute(attr, value);
  return true;
}


/**
 * Render a placeholder for an obfuscated element
 */
function maybeRenderPlaceholder(el: ObfuscatedElement): void {
  if (el.hasAttribute("attr")) return;

  if (!renderPlaceholders) return;

  const charCount = parseInt(el.getAttribute("char-count") ?? "0", 10);
  const span = document.createElement("span");
  span.textContent = "";
  for (let i = 0; i < charCount; i++) {
    const clone = span.cloneNode(true);
    el.append(clone);
  }
}