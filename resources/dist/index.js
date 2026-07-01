//#region resources/src/helpers.ts
const prefix = "html-obfuscator";
const key = document.currentScript?.getAttribute("data-key");
const keyLength = key.length;
const createLogger = () => {
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
};
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
const decode = (el) => {
	const value = atob(el.getAttribute("value") ?? "");
	if (!value) return;
	return [...value].map((c, i) => String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % keyLength))).join("");
};

//#endregion
//#region resources/src/ObfuscatedElement.ts
/**
* Render an obfuscated element that can reveal itself or a parent element's attribute
*/
var ObfuscatedElement = class extends HTMLElement {
	/** the decoded value, as a private property */
	#decodedValue;
	get attr() {
		return this.getAttribute("attr");
	}
	constructor() {
		super();
		this.reveal = () => {
			if (!this.#decodedValue) {
				this.remove();
				return;
			}
			if (revealAttribute(this, this.#decodedValue)) {
				this.remove();
				return;
			}
			this.outerHTML = this.#decodedValue;
		};
		this.shadow = this.attachShadow({ mode: "closed" });
	}
	connectedCallback() {
		this.#decodedValue = decode(this);
		if (!this.#decodedValue) {
			this.remove();
			return;
		}
		if (!this.attr) this.shadow.textContent = this.#decodedValue;
		detectGlobalInteraction().then(this.reveal);
	}
};
/**
* Reveal a parent element's attribute value
*/
function revealAttribute(el, value) {
	const attr = el.attr;
	if (!attr) return false;
	const target = el.parentElement?.closest(`[${attr}]`);
	if (!target) return false;
	target.setAttribute(attr, value);
	return true;
}

//#endregion
//#region resources/src/index.ts
/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */
const logger = createLogger();
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

//#endregion