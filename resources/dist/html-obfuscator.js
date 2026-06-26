(function() {


//#region resources/src/helpers.ts
	var prefix = "html-obfuscator";
	var defaults = {
		tagName: "x-obfuscated",
		debug: false,
		revealStrategy: "onload",
		renderPlaceholders: false
	};
	var settings = (() => {
		const attr = document.currentScript?.getAttribute("data-settings");
		if (!attr) return defaults;
		try {
			return JSON.parse(attr);
		} catch (e) {
			return defaults;
		}
	})();
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
	function applyStyles(el, styles) {
		Object.assign(el.style, styles);
	}
	function detectGlobalInteraction() {
		return detectInteraction(window);
	}
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
					}, { signal: abortCtrl.signal });
				});
			}));
			return promises.get(target);
		};
	})();

//#endregion
//#region resources/src/ObfuscatedElement.ts
	var { revealStrategy: revealStrategy$1 } = settings;
	var ObfuscatedElement = class extends HTMLElement {
		constructor(..._args) {
			super(..._args);
			this.reveal = () => {
				const value = atob(this.getAttribute("value") ?? "");
				const key = this.getAttribute("key");
				if (!value || !key) {
					this.remove();
					return;
				}
				const result = [...value].map((c, i) => String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length))).join("");
				this.outerHTML = result;
			};
		}
		connectedCallback() {
			if (revealStrategy$1 === "onload") return this.reveal();
			renderPlaceholder(this);
			if (revealStrategy$1 === "oninteraction") detectGlobalInteraction().then(this.reveal);
		}
	};
	function renderPlaceholder(el) {
		applyStyles(el, {
			display: "inline-flex",
			alignItems: "center",
			flexWrap: "wrap",
			cursor: "pointer"
		});
		const charCount = parseInt(el.getAttribute("char-count") ?? "0", 10);
		const span = document.createElement("span");
		applyStyles(span, {
			display: "inline-block",
			width: "0.38ch",
			height: "1em",
			background: "black"
		});
		for (let i = 0; i < charCount; i++) {
			const clone = span.cloneNode();
			el.append(clone);
		}
	}

//#endregion
//#region resources/src/html-obfuscator.ts
	logger?.log(settings);
	var { tagName, revealStrategy } = settings;
	if (revealStrategy === "oninteraction") detectGlobalInteraction().then(() => {
		logger?.log("User has interacted");
	});
	try {
		window.customElements.define(tagName, ObfuscatedElement);
	} catch (e) {
		logger?.error(e);
	}

//#endregion
})();