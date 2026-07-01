/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */

import { createLogger, detectGlobalInteraction } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

// @ts-ignore will be replaced by rolldown at build time
const debug = process.env.NODE_ENV === "development";
const logger = debug ? createLogger() : undefined;

const key = "fill-me";
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
