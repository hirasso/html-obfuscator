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

    this.renderPlaceholder();

    if (revealStrategy === "oninteraction") {
      detectGlobalInteraction().then(this.reveal);
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

  /**
   * Render a placeholder for this element
   */
  renderPlaceholder(): void {
    if (!renderPlaceholders) return;

    const charCount = parseInt(this.getAttribute("char-count") ?? "0", 10);
    const span = document.createElement("span");
    span.textContent = "";
    for (let i = 0; i < charCount; i++) {
      const clone = span.cloneNode(true);
      this.append(clone);
    }
  }
}
