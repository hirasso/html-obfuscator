"use strict";

// @ts-check

class ObfuscatedElement extends HTMLElement {
  connectedCallback() {
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

if (!window.customElements.get("obfuscated-element")) {
  window.customElements.define("obfuscated-element", ObfuscatedElement);
}
