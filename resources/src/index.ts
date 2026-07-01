/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */

import {
  createLogger,
  detectGlobalInteraction,
  settings,
} from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

// @ts-ignore will be replaced by rolldown at build time
const debug = process.env.NODE_ENV === "development";
const logger = debug ? createLogger() : undefined;

logger?.log(settings);

const { tagName } = settings;

detectGlobalInteraction().then(() => {
  logger?.log("User has interacted");
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
