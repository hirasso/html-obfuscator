"use strict";

// @ts-check

(function () {
  const tagName = "x-obfuscated";

  if (window.customElements.get(tagName)) {
    return;
  }

  let hasInteracted = false;

  window.customElements.define(
    tagName,
    class extends HTMLElement {
      abortController = new AbortController();

      static get observedAttributes() {
        return ["require-interaction"];
      }

      connectedCallback() {
        if (!this.requireInteraction()) {
          this.render();
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

        if (hasInteracted || !requiredInteraction) {
          return false;
        }

        document.addEventListener(
          "html-obfuscator:render",
          this.render,
          this.abortController,
        );

        switch (requiredInteraction) {
          case "onElement":
            this.addEventListener("focusin", this.render, this.abortController);
            break;

          case "onDocument":
            ["pointermove", "pointerdown", "keydown"].forEach((evt) =>
              document.addEventListener(evt, this.render, this.abortController),
            );
            break;

          default:
            break;
        }

        return true;
      }

      render = () => {
        const value = atob(this.getAttribute("value") ?? "");
        const key = this.getAttribute("key");

        if (!value || !key) {
          this.remove();
          return;
        }

        const result = [...value]
          .map((c, i) =>
            String.fromCharCode(
              c.charCodeAt(0) ^ key.charCodeAt(i % key.length),
            ),
          )
          .join("");

        this.outerHTML = result;

        document.dispatchEvent(new CustomEvent("html-obfuscator:render"));
      };
    },
  );
})();
