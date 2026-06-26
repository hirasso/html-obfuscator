/**
 * This is the frontend script of html-obfuscator
 * It will detect existing and new <x-obfuscated> elements
 * and reveal them automatically.
 */

import { detectGlobalInteraction, settings, logger } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

console.log(settings);
logger?.log(settings);

const { tagName, revealStrategy } = settings;

if (revealStrategy === "oninteraction") {
  detectGlobalInteraction().then(() => {
    logger?.log("User has interacted");
  });
}

/**
 * Define the custom element, logging errors only in debug mode
 */
try {
  window.customElements.define(tagName, ObfuscatedElement);
} catch (e) {
  logger?.error(e);
}
