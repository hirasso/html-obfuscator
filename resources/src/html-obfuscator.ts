/**
 * This is the frontend script of html-obfuscator
 * It will detect existing and new <x-obfuscated> elements
 * and reveal them automatically.
 */

import { detectGlobalInteraction, dispatchPrefixedEvent } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

const currentScript = document.currentScript!;
const debug = (() => {
  return currentScript.hasAttribute("debug") ? console : null;
})();
const defaultTagName = "x-obfuscated";
const tagName = currentScript!.getAttribute("tag-name") ?? defaultTagName;
debug?.log(`using tag name ${tagName}`);

detectGlobalInteraction().then(() => {
  debug?.log('User has interacted');
});

/**
 * Define the custom element, logging errors only in debug mode
 */
try {
  window.customElements.define(tagName, ObfuscatedElement);
} catch (e) {
  debug?.error(e);
}
