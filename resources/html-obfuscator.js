"use strict";

// @ts-check

(function () {
  const tagName = "x-obfuscated";

  let hasInteracted = false;
  let hasIntentionallyInteracted = false;

  (function () {
    const c = new AbortController();

    /** @param {Event} e */
    const handler = (e) => {
      c.abort();
      hasInteracted = true;

      if (e.target instanceof Element && e.target?.closest('[require-interaction="intentional"]')) {
        hasIntentionallyInteracted = true;
      }

      document
        .querySelectorAll(tagName)
        .forEach((el) => el.removeAttribute("require-interaction"));
    };

    [
      "pointermove",
      "pointerdown",
      "keydown",
    ].forEach((evt) => {
      document.addEventListener(evt, handler, c);
    });
  })();

  class ObfuscatedElement extends HTMLElement {
    static get observedAttributes() {
      return ["require-interaction"];
    }

    connectedCallback() {
      if (!this.needsInteraction) {
        this.render();
      }
    }

    /**
     * @param {string} name
     * @param {string} oldValue
     * @param {string} newValue
     */
    attributeChangedCallback(name, oldValue, newValue) {
      if (name === "require-interaction" && newValue === null) {
        this.render();
      }
    }

    get needsInteraction() {
      if (hasInteracted || !this.hasAttribute("require-interaction")) {
        return false;
      }

      return true;
    }

    render() {
      const value = atob(this.getAttribute("value") ?? "");
      const key = this.getAttribute("key");

      if (!value || !key) {
        console.error("No value or key provided, destroying...");
        this.remove();
        return;
      }

      let result = "";
      for (let i = 0; i < value.length; i++)
        result += String.fromCharCode(
          value.charCodeAt(i) ^ key.charCodeAt(i % key.length),
        );

      this.outerHTML = result;
    }
  }

  if (!window.customElements.get(tagName)) {
    window.customElements.define(tagName, ObfuscatedElement);
  }
})();
