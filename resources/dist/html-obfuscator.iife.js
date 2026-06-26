(function() {
	//#region resources/src/helpers.ts
	/**
	* Apply styles to an element, with intellisense support
	*/
	function applyStyles(el, styles) {
		Object.assign(el.style, styles);
	}
	/**
	* Prefix a string with our prefix
	*/
	function prefixed(str) {
		return `html-obfuscator:${str}`;
	}
	/**
	* Dispatch custom prefixed events
	*/
	function dispatchPrefixedEvent(eventName) {
		document.documentElement.dispatchEvent(new CustomEvent(prefixed(eventName), { bubbles: true }));
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
					}, { signal: abortCtrl.signal });
				});
			}));
			return promises.get(target);
		};
	})();
	//#endregion
	//#region \0@oxc-project+runtime@0.133.0/helpers/esm/typeof.js
	function _typeof(o) {
		"@babel/helpers - typeof";
		return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(o) {
			return typeof o;
		} : function(o) {
			return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
		}, _typeof(o);
	}
	//#endregion
	//#region \0@oxc-project+runtime@0.133.0/helpers/esm/toPrimitive.js
	function toPrimitive(t, r) {
		if ("object" != _typeof(t) || !t) return t;
		var e = t[Symbol.toPrimitive];
		if (void 0 !== e) {
			var i = e.call(t, r || "default");
			if ("object" != _typeof(i)) return i;
			throw new TypeError("@@toPrimitive must return a primitive value.");
		}
		return ("string" === r ? String : Number)(t);
	}
	//#endregion
	//#region \0@oxc-project+runtime@0.133.0/helpers/esm/toPropertyKey.js
	function toPropertyKey(t) {
		var i = toPrimitive(t, "string");
		return "symbol" == _typeof(i) ? i : i + "";
	}
	//#endregion
	//#region \0@oxc-project+runtime@0.133.0/helpers/esm/defineProperty.js
	function _defineProperty(e, r, t) {
		return (r = toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
			value: t,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : e[r] = t, e;
	}
	//#endregion
	//#region resources/src/html-obfuscator.ts
	/**
	* This is the frontend script of html-obfuscator
	* It will detect existing and new <x-obfuscated> elements
	* and reveal them automatically.
	*/
	var currentScript = document.currentScript;
	var debug = (() => {
		return currentScript.hasAttribute("debug") ? console : null;
	})();
	var tagName = currentScript.getAttribute("tag-name") ?? "x-obfuscated";
	debug?.log(`using tag name ${tagName}`);
	/** Detect interaction globally */
	var hasInteractedGlobally = false;
	detectInteraction(document.documentElement).then(() => {
		debug?.log("user has interacted, dispatching reveal event...");
		hasInteractedGlobally = true;
		dispatchPrefixedEvent("reveal");
	});
	var ObfuscatedElement = class extends HTMLElement {
		constructor(..._args) {
			super(..._args);
			_defineProperty(this, "abortController", new AbortController());
			_defineProperty(this, "reveal", () => {
				const value = atob(this.getAttribute("value") ?? "");
				const key = this.getAttribute("key");
				if (!value || !key) {
					this.remove();
					return;
				}
				const result = [...value].map((c, i) => String.fromCharCode(c.charCodeAt(0) ^ key.charCodeAt(i % key.length))).join("");
				this.outerHTML = result;
				dispatchPrefixedEvent("reveal");
			});
		}
		static get observedAttributes() {
			return ["require-interaction"];
		}
		connectedCallback() {
			if (!this.requireInteraction()) this.reveal();
		}
		disconnectedCallback() {
			this.abortController.abort();
		}
		attributeChangedCallback() {
			this.connectedCallback();
		}
		requireInteraction() {
			const requiredInteraction = this.getAttribute("require-interaction");
			if (hasInteractedGlobally || !requiredInteraction) return false;
			this.showPlaceholder();
			document.addEventListener("html-obfuscator:reveal", this.reveal, this.abortController);
			switch (requiredInteraction) {
				case "onElement":
					this.addEventListener("focusin", this.reveal, this.abortController);
					break;
				default: break;
			}
			return true;
		}
		showPlaceholder() {
			applyStyles(this, {
				display: "inline-flex",
				alignItems: "center",
				flexWrap: "wrap",
				cursor: "pointer"
			});
			const charCount = parseInt(this.getAttribute("char-count") ?? "0", 10);
			const span = document.createElement("span");
			applyStyles(span, {
				display: "inline-block",
				width: "0.38ch",
				height: "1em",
				background: "black"
			});
			for (let i = 0; i < charCount; i++) {
				const clone = span.cloneNode();
				this.append(clone);
			}
		}
	};
	/**
	* Define the custom element, logging errors only in debug mode
	*/
	try {
		window.customElements.define(tagName, ObfuscatedElement);
	} catch (e) {
		debug?.error(e);
	}
	//#endregion
})();
