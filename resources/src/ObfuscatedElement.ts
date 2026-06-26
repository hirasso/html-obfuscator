import {
  applyStyles,
  detectGlobalInteraction,
  settings,
} from "./helpers.js";

const { revealStrategy } = settings;

/**
 * Renders an obfuscated element that can reveal itself
 */
export class ObfuscatedElement extends HTMLElement {

  connectedCallback() {
    if (revealStrategy === "onload") {
      return this.reveal();
    }

    renderPlaceholder(this);

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

/**
 * Render a placeholder (private method)
 */
function renderPlaceholder(el: ObfuscatedElement) {
  applyStyles(el, {
    display: "inline-flex",
    alignItems: "center",
    flexWrap: "wrap",
    cursor: "pointer",
  });

  const charCount = parseInt(el.getAttribute("char-count") ?? "0", 10);

  const span = document.createElement("span");
  applyStyles(span, {
    display: "inline-block",
    width: "0.38ch",
    height: "1em",
    background: "black",
  });

  for (let i = 0; i < charCount; i++) {
    const clone = span.cloneNode();
    el.append(clone);
  }
}
