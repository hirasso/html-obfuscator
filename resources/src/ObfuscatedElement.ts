import { applyStyles, detectGlobalInteraction } from "./helpers.js";

/**
 * Renders an obfuscated element that can reveal itself
 */
export class ObfuscatedElement extends HTMLElement {
  abortController = new AbortController();

  static get observedAttributes() {
    return ["require-interaction"];
  }

  connectedCallback() {
    if (!this.requireInteraction()) {
      this.reveal();
    }
  }

  disconnectedCallback() {
    this.abortController.abort();
  }

  attributeChangedCallback() {
    this.connectedCallback();
  }

  requireInteraction() {
    const requiredInteraction = this.getAttribute("require-interaction");

    if (!requiredInteraction) {
      return false;
    }

    this.showPlaceholder();

    detectGlobalInteraction().then(this.reveal);

    return true;
  }

  showPlaceholder() {
    applyStyles(this, {
      display: "inline-flex",
      alignItems: "center",
      flexWrap: "wrap",
      cursor: "pointer",
    });

    const charCount = parseInt(this.getAttribute("char-count") ?? "0", 10);

    const span = document.createElement("span");
    applyStyles(span, {
      display: "inline-block",
      width: "0.38ch",
      height: "1em",
      background: "black",
    });

    for (let i = 0; i < charCount; i++) {
      const clone = span.cloneNode();
      this.append(clone);
    }
  }

  reveal = () => {
    const value = atob(this.getAttribute("value") ?? "");
    const key = this.getAttribute("key");

    if (!value || !key) {
      this.remove();
      return;
    }

    const result = [...value]
      .map((c, i) =>
        String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length)),
      )
      .join("");

    this.outerHTML = result;
  };
}
