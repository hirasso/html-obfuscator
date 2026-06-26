import {
  detectGlobalInteraction,
  renderPlaceholder,
  settings,
} from "./helpers.js";

const { revealStrategy, renderPlaceholders } = settings;

/**
 * Renders an obfuscated element that can reveal itself
 */
export class ObfuscatedElement extends HTMLElement {

  connectedCallback() {
    if (revealStrategy === "onload") {
      return this.reveal();
    }

    if (renderPlaceholders) {
      renderPlaceholder(this);
    }

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
}

