//#region resources/src/helpers.ts
const prefix = "html-obfuscator";
const logger = createLogger();
const key = document.currentScript?.getAttribute("data-key") ?? "";
if (!key) throw new Error("No key provided");
function createLogger() {
	const style = [
		"background: linear-gradient(to right, #a960ee, #f78ed4)",
		"color: white",
		"padding-inline: 4px",
		"border-radius: 2px",
		"font-family: monospace"
	].join(";");
	return {
		log: (...args) => console.log(`%c${prefix}`, style, ...args),
		warn: (...args) => console.warn(`%c${prefix}`, style, ...args),
		error: (...args) => console.error(`%c${prefix}`, style, ...args)
	};
}
/**
* Detect interaction anywhere on the window
*/
function detectGlobalInteraction() {
	return detectInteraction(window);
}
/**
* Detect interaction. Dedupes and short-circuits
* repeated calls against the same element.
*/
const detectInteraction = (() => {
	let hasInteracted = false;
	const promises = /* @__PURE__ */ new Map();
	return (target, events = void 0) => {
		events ??= [
			"pointermove",
			"pointerdown",
			"keydown"
		];
		if (hasInteracted) return Promise.resolve(target);
		if (!promises.has(target)) promises.set(target, new Promise((resolve) => {
			const abortCtrl = new AbortController();
			events.forEach((eventName) => {
				target.addEventListener(eventName, () => {
					abortCtrl.abort();
					hasInteracted = true;
					resolve(target);
				}, { signal: abortCtrl.signal });
			});
		}));
		return promises.get(target);
	};
})();
/**
* Decode a value
*/
const decode = (() => {
	const cache = /* @__PURE__ */ new Map();
	return (el, logger) => {
		const encoded = atob(el.getAttribute("value") ?? "");
		if (!encoded) return;
		if (cache.has(encoded)) {
			logger?.log(`Cache hit for ${encoded}`);
			return cache.get(encoded);
		}
		const decoded = [...encoded].map((c, i) => String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length))).join("");
		cache.set(encoded, decoded);
		return decoded;
	};
})();

//#endregion
//#region resources/src/ObfuscatedElement.ts
/**
* Render an obfuscated element that can reveal itself or a parent element's attribute
*/
var ObfuscatedElement = class extends HTMLElement {
	get attr() {
		return this.getAttribute("attr");
	}
	constructor() {
		super();
		this.shadow = this.attachShadow({ mode: "closed" });
	}
	connectedCallback() {
		const decoded = decode(this, logger);
		if (!decoded) {
			this.remove();
			return;
		}
		if (!this.attr) this.shadow.textContent = decoded;
		detectGlobalInteraction().then(() => {
			/** plaintext */
			if (!this.attr) {
				this.outerHTML = decoded;
				return;
			}
			/** attribute */
			this.parentElement?.closest(`[${this.attr}]`)?.setAttribute(this.attr, decoded);
			/** cleanup */
			this.remove();
		});
	}
};

//#endregion
//#region resources/src/index.ts
/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */
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
if (!true) document.currentScript?.remove();

//#endregion