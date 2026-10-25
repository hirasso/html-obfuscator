/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */

import { debug } from "./defs.js";
import { tagName } from "./generated/constants.js";
import { createLogger, detectGlobalInteraction, dispatch } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

const logger = debug ? createLogger() : undefined;

(() => {
  logger?.log({ tagName });

  detectGlobalInteraction().then(() => {
    dispatch('reveal');
    logger?.log("Interaction detected. Obfuscated content revealed.");
  });

  ObfuscatedElement.register(tagName, logger);

  /**
   * Remove this script from the DOM immediately after execution when not in debug mode
   */
  if (!debug) {
    document.currentScript?.remove();
  }
})();
