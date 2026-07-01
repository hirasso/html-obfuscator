import { decode, detectGlobalInteraction } from "./helpers.js";

/**
 * Render an obfuscated element that can reveal itself or a parent element's attribute
 */
export class ObfuscatedElement extends HTMLElement {
  shadow: ShadowRoot;

  /** the decoded value, as a private property */
  #decodedValue: string | undefined;

  get attr() {
    return this.getAttribute("attr");
  }

  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: "closed" });
  }

  connectedCallback() {
    this.#decodedValue = decode(this);

    if (!this.#decodedValue) {
      this.remove();
      return;
    }

    if (!this.attr) {
      this.shadow.textContent = this.#decodedValue;
    }

    detectGlobalInteraction().then(this.reveal);
  }

  reveal = () => {
    if (!this.#decodedValue) {
      this.remove();
      return;
    }

    if (revealAttribute(this, this.#decodedValue)) {
      this.remove();
      return;
    }

    this.outerHTML = this.#decodedValue;
  };
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
