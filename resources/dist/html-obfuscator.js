(function() {


//#region resources/src/helpers.ts
	var prefix = "html-obfuscator";
	var defaults = {
		tagName: "x-obfuscated",
		debug: false,
		revealStrategy: "onload",
		renderPlaceholders: false
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
		const store = /* @__PURE__ */ new Map();
		return function(selector) {
			if (store.has(selector)) return store.get(selector);
			const el = document.getElementById(selector);
			if (!el) throw new Error(`No script data element found for "${selector}"`);
			let value;
			try {
				value = JSON.parse(el.textContent ?? "");
			} catch {
				throw new Error(`Failed to parse script data for "${selector}"`);
			}
			if (!value.settings) throw new Error(`No settings found in script data for "${selector}"`);
			store.set(selector, value.settings);
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

//#endregion
//#region resources/src/ObfuscatedElement.ts
	var { revealStrategy: revealStrategy$1, renderPlaceholders } = settings;
	/**
	* Render an obfuscated element that can reveal itself or a parent element's attribute
	*/
	var ObfuscatedElement = class extends HTMLElement {
		constructor(..._args) {
			super(..._args);
			this.reveal = () => {
				const value = this.decode();
				if (!value) {
					this.remove();
					return;
				}
				if (revealAttribute(this, value)) {
					this.remove();
					return;
				}
				this.outerHTML = value;
			};
			this.decode = (() => {
				let value;
				return () => {
					return value ?? (value = getDecodedValue(this));
				};
			})();
		}
		get attr() {
			return this.getAttribute("attr");
		}
		connectedCallback() {
			if (revealStrategy$1 === "onload") return this.reveal();
			if (renderPlaceholders && !this.attr) renderPlaceholder(this);
			if (revealStrategy$1 === "oninteraction") detectGlobalInteraction().then(this.reveal);
		}
	};
	/**
	* Get the decoded value
	*/
	function getDecodedValue(el) {
		const value = atob(el.getAttribute("value") ?? "");
		const key = el.getAttribute("key");
		if (!value || !key) {
			el.remove();
			return;
		}
		return [...value].map((c, i) => String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length))).join("");
	}
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
	/**
	* Render a placeholder for an obfuscated element
	*/
	function renderPlaceholder(el) {
		const value = getDecodedValue(el);
		if (!value) return;
		const ctx = document.createElement("canvas").getContext("2d");
		ctx.font = getComputedStyle(el).font;
		const oneChWidth = ctx.measureText("0").width;
		for (const char of value) {
			const span = document.createElement("span");
			span.style.overflow = "hidden";
			span.style.display = "inline-block";
			span.style.whiteSpace = "pre";
			span.style.width = `${ctx.measureText(char).width / oneChWidth}ch`;
			span.textContent = "\xA0";
			el.append(span);
		}
	}

//#endregion
//#region resources/src/html-obfuscator.ts
/*! hirasso/html-obfuscator | MIT License | Copyright (c) 2026 Rasso Hilber <mail@rassohilber.com> */
	logger?.log(settings);
	var { tagName, revealStrategy } = settings;
	if (revealStrategy === "oninteraction") detectGlobalInteraction().then(() => {
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

//#endregion
})();