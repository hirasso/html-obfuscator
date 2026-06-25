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

        this.renderAsPlaceholder();

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

      renderAsPlaceholder() {
        setStyles(this, {
          display: "inline-flex",
          alignItems: "center",
          flexWrap: "wrap",
          cursor: "pointer",
        });

        const charCount = parseInt(this.getAttribute("char-count") ?? "0", 10);

        const span = document.createElement("span");
        setStyles(span, {
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

  /**
   * @param {HTMLElement} el,
   * @param {Partial<CSSStyleDeclaration>} styles
   * @return {void}
   */
  function setStyles(el, styles) {
    Object.assign(el.style, styles);
  }
})();
