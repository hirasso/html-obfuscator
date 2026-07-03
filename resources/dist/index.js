//#region resources/src/defs.ts
const tagName = document.currentScript?.getAttribute("data-tagname") ?? "";
const key = document.currentScript?.getAttribute("data-key") ?? "";

//#endregion
//#region resources/src/helpers.ts
const prefix = "html-obfuscator";
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
* Prefix a string with our prefix
*/
function prefixed(str) {
	return `${prefix}:${str}`;
}
/**
* Dispatch custom prefixed events
*/
function dispatch(eventName) {
	document.documentElement.dispatchEvent(new CustomEvent(prefixed(eventName), { bubbles: true }));
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
	return (target, events = [
		"pointermove",
		"pointerdown",
		"keydown"
	]) => {
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
		const raw = el.getAttribute("value");
		if (!raw) return;
		const [value, key] = raw.split(":xor:");
		const encoded = atob(value);
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
let logger$1;
/**
* Render an obfuscated element that can reveal itself or a parent element's attribute
*/
var ObfuscatedElement = class ObfuscatedElement extends HTMLElement {
	/**
	* Define the custom element
	*/
	static register(tagName, logger) {
		logger = logger;
		try {
			window.customElements.define(tagName, ObfuscatedElement);
		} catch (e) {
			logger?.error(e);
		}
	}
	get attr() {
		return this.getAttribute("attr");
	}
	constructor() {
		super();
		this.shadow = this.attachShadow({ mode: "closed" });
	}
	connectedCallback() {
		const decoded = decode(this, logger$1);
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
const logger = true ? createLogger() : void 0;
(() => {
	if (!key || !tagName) {
		logger?.error("required properties are missing:", {
			tagName,
			key
		});
		return;
	}
	logger?.log({
		tagName,
		key
	});
	detectGlobalInteraction().then(() => {
		dispatch("reveal");
		logger?.log("Interaction detected. Obfuscated content revealed.");
	});
	ObfuscatedElement.register(tagName, logger);
	/**
	* Remove this script from the DOM immediately after execution when not in debug mode
	*/
	if (!true) document.currentScript?.remove();
})();

//#endregion