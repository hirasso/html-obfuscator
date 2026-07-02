import { decode, detectGlobalInteraction, Logger } from "./helpers.js";

let logger: Logger | undefined;

/**
 * Render an obfuscated element that can reveal itself or a parent element's attribute
 */
export class ObfuscatedElement extends HTMLElement {
  shadow: ShadowRoot;

  /**
   * Define the custom element
   */
  static register(tagName: string, logger?: Logger) {
    logger = logger;
    try {
      window.customElements.define(tagName, ObfuscatedElement);
    } catch (e) {
      logger?.error(e);
    }
  }

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
