/**
 * This is the frontend script of html-obfuscator
 * It will detect existing and new <x-obfuscated> elements
 * and reveal them automatically.
 */

import { applyStyles, detectInteraction, dispatchPrefixedEvent } from "./helpers.js";

const currentScript = document.currentScript!;
const debug = (() => {
  return currentScript.hasAttribute("debug") ? console : null;
})();
const defaultTagName = "x-obfuscated";
const tagName = currentScript!.getAttribute("tag-name") ?? defaultTagName;
debug?.log(`using tag name ${tagName}`);

/** Detect interaction globally */
let hasInteractedGlobally = false;
detectInteraction(document.documentElement).then(() => {
  debug?.log('user has interacted, dispatching reveal event...');
  hasInteractedGlobally = true;
  dispatchPrefixedEvent("reveal");
});

class ObfuscatedElement extends HTMLElement {
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

    if (hasInteractedGlobally || !requiredInteraction) {
      return false;
    }

    this.showPlaceholder();

    document.addEventListener(
      "html-obfuscator:reveal",
      this.reveal,
      this.abortController,
    );

    switch (requiredInteraction) {
      case "onElement":
        this.addEventListener("focusin", this.reveal, this.abortController);
        break;

      default:
        break;
    }

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

    dispatchPrefixedEvent('reveal');
  };
}

/**
 * Define the custom element, logging errors only in debug mode
 */
try {
  window.customElements.define(tagName, ObfuscatedElement);
} catch (e) {
  debug?.error(e);
}
