(function() {


//#region resources/src/helpers.ts
	var prefix = "html-obfuscator";
	var defaults = {
		tagName: "x-obfuscated",
		debug: false
	};
	/**
	* Load the settings from the script tag
	*/
	var settings = (() => {
		const attr = document.currentScript?.getAttribute("data-settings");
		if (!attr) return defaults;
		try {
			return JSON.parse(attr);
		} catch (e) {
			return defaults;
		}
	})();
	/**
	* Get a minimal logger with a prefix, if settings.debug = true
	*/
	var logger = (() => {
		if (!settings.debug) return null;
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
	})();
	/**
	* Load the data from a json script tag
	*/
	var loadSettingsFromJsonScriptTag = (() => {
		const cache = /* @__PURE__ */ new Map();
		return function(selector) {
			if (cache.has(selector)) return cache.get(selector);
			const el = document.getElementById(selector);
			if (!el) throw new Error(`No script data element found for "${selector}"`);
			let value;
			try {
				value = JSON.parse(el.textContent ?? "");
			} catch {
				throw new Error(`Failed to parse script data for "${selector}"`);
			}
			if (!value.settings) throw new Error(`No settings found in script data for "${selector}"`);
			cache.set(selector, value.settings);
			return value.settings;
		};
	})();
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
	var detectInteraction = (() => {
		let hasInteracted = false;
		const promises = /* @__PURE__ */ new Map();
		return (target, events = void 0) => {
			events ?? (events = [
				"pointermove",
				"pointerdown",
				"keydown"
			]);
			if (hasInteracted) return Promise.resolve(target);
			if (!promises.has(target)) promises.set(target, new Promise((resolve) => {
				const abortCtrl = new AbortController();
				events.forEach((eventName) => {
					target.addEventListener(eventName, () => {
						abortCtrl.abort();
						hasInteracted = true;
						resolve(target);
						logger?.log({ eventName });
					}, { signal: abortCtrl.signal });
				});
			}));
			return promises.get(target);
		};
	})();
	/**
	* Decode a value
	*/
	var decode = (el) => {
		const value = atob(el.getAttribute("value") ?? "");
		const key = el.getAttribute("key");
		if (!value || !key) return;
		return [...value].map((c, i) => String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length))).join("");
	};

//#endregion
//#region \0@oxc-project+runtime@0.133.0/helpers/esm/checkPrivateRedeclaration.js
	function _checkPrivateRedeclaration(e, t) {
		if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object");
	}

//#endregion
//#region \0@oxc-project+runtime@0.133.0/helpers/esm/classPrivateFieldInitSpec.js
	function _classPrivateFieldInitSpec(e, t, a) {
		_checkPrivateRedeclaration(e, t), t.set(e, a);
	}

//#endregion
//#region \0@oxc-project+runtime@0.133.0/helpers/esm/assertClassBrand.js
	function _assertClassBrand(e, t, n) {
		if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n;
		throw new TypeError("Private element is not present on this object");
	}

//#endregion
//#region \0@oxc-project+runtime@0.133.0/helpers/esm/classPrivateFieldGet2.js
	function _classPrivateFieldGet2(s, a) {
		return s.get(_assertClassBrand(s, a));
	}

//#endregion
//#region \0@oxc-project+runtime@0.133.0/helpers/esm/classPrivateFieldSet2.js
	function _classPrivateFieldSet2(s, a, r) {
		return s.set(_assertClassBrand(s, a), r), r;
	}

//#endregion
//#region resources/src/ObfuscatedElement.ts
	var _decodedValue = /* @__PURE__ */ new WeakMap();
	/**
	* Render an obfuscated element that can reveal itself or a parent element's attribute
	*/
	var ObfuscatedElement = class extends HTMLElement {
		get attr() {
			return this.getAttribute("attr");
		}
		constructor() {
			super();
			_classPrivateFieldInitSpec(this, _decodedValue, void 0);
			this.reveal = () => {
				if (!_classPrivateFieldGet2(_decodedValue, this)) {
					this.remove();
					return;
				}
				if (revealAttribute(this, _classPrivateFieldGet2(_decodedValue, this))) {
					this.remove();
					return;
				}
				this.outerHTML = _classPrivateFieldGet2(_decodedValue, this);
			};
			this.shadow = this.attachShadow({ mode: "closed" });
		}
		connectedCallback() {
			_classPrivateFieldSet2(_decodedValue, this, decode(this));
			if (!_classPrivateFieldGet2(_decodedValue, this)) {
				this.remove();
				return;
			}
			if (!this.attr) this.shadow.textContent = _classPrivateFieldGet2(_decodedValue, this);
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
//#region resources/src/html-obfuscator.ts
/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */
	logger?.log(settings);
	var { tagName } = settings;
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
	* Remove this script from the DOM immediately after execution
	*/
	document.currentScript?.remove();

//#endregion
})();