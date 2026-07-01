import { decode, detectGlobalInteraction, logger } from "./helpers.js";

/**
 * Render an obfuscated element that can reveal itself or a parent element's attribute
 */
export class ObfuscatedElement extends HTMLElement {
  shadow: ShadowRoot;

  get attr() {
    return this.getAttribute("attr");
  }

  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: "closed" });
  }

  connectedCallback() {
    const decoded = decode(this, logger);

    if (!decoded) {
      this.remove();
      return;
    }

    if (!this.attr) {
      this.shadow.textContent = decoded;
    }

    detectGlobalInteraction().then(() => {
      /** plaintext */
      if (!this.attr) {
        this.outerHTML = decoded;
        return;
      }

      /** attribute */
      this.parentElement
        ?.closest(`[${this.attr}]`)
        ?.setAttribute(this.attr, decoded);

      /** cleanup */
      this.remove();
    });
  }
}
