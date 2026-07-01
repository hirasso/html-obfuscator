/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */

import { detectGlobalInteraction, debug, logger } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

const tagName = "x-obfuscated";

logger?.log({ tagName });

detectGlobalInteraction().then(() => {
  logger?.log("Interaction detected, lifting obfuscation");
});

/**
 * Define the custom element, logging errors only in debug mode
 */
try {
  window.customElements.define(tagName, ObfuscatedElement);
} catch (e) {
  logger?.error(e);
}

/**
 * Remove this script from the DOM immediately after execution when not in debug mode
 */
if (!debug) {
  document.currentScript?.remove();
}
