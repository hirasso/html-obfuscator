/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */

import { debug, key, tagName } from "./env.js";
import { detectGlobalInteraction, logger } from "./helpers.js";
import { ObfuscatedElement } from "./ObfuscatedElement.js";

(() => {
  if (!key || !tagName) {
    logger?.error("required properties are missing:", { tagName, key });
    return;
  }

  logger?.log({ tagName, key });

  detectGlobalInteraction().then(() => {
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
