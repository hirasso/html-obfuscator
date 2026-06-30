/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */

import { detectGlobalInteraction, settings, logger } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

import './html-obfuscator.css';

logger?.log(settings);

const { tagName, revealTrigger } = settings;

if (revealTrigger === "interaction") {
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
