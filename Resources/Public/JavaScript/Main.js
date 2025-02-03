var Main;
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "../Resources/Private/JavaScript/ContentBlocker.js":
/*!*********************************************************!*\
  !*** ../Resources/Private/JavaScript/ContentBlocker.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ContentBlocker)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var ContentBlocker = /*#__PURE__*/function () {
  function ContentBlocker($element) {
    var _this = this;
    _classCallCheck(this, ContentBlocker);
    this.$element = $element;
    var $allowButton = jquery__WEBPACK_IMPORTED_MODULE_0___default()(".contentAllowButton", this.$element);
    if (this.getMode() == "cookieman" && window.cookieman) {
      if (cookieman.hasConsented(this.getCookieName())) {
        ContentBlocker.loadContent(this.$element);
      }
    } else {
      if (this.getCookie(this.getCookieName())) {
        ContentBlocker.loadContent(this.$element);
      }
    }
    $allowButton.on("click", function (e) {
      e.preventDefault();
      _this.allow();
    });
  }
  return _createClass(ContentBlocker, [{
    key: "allow",
    value: function allow() {
      var $alwaysCheckbox = jquery__WEBPACK_IMPORTED_MODULE_0___default()('.allow-always', this.$element);
      if (this.getMode() == "cookieman" && window.cookieman) {
        if ($alwaysCheckbox.is(':checked')) {
          cookieman.consent(this.getConsentGroupId());
        }
      } else {
        this.setCookie(this.getCookieName(), true, 30);
      }
      ContentBlocker.loadContent(this.$element);
    }
  }, {
    key: "getCookieName",
    value: function getCookieName() {
      return this.getConsentGroupId() + "-allowed";
    }
  }, {
    key: "getConsentGroupId",
    value: function getConsentGroupId() {
      return this.$element.data("consent-group-id");
    }
  }, {
    key: "getMode",
    value: function getMode() {
      return this.$element.data("content-blocker-mode");
    }
  }, {
    key: "setCookie",
    value: function setCookie(name, value, days) {
      var expires = "";
      if (days) {
        var date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        expires = "; expires=" + date.toUTCString();
      }
      document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
  }, {
    key: "getCookie",
    value: function getCookie(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
    }
  }, {
    key: "eraseCookie",
    value: function eraseCookie(name) {
      document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
  }], [{
    key: "mount",
    value: function mount() {
      var $blockContentElements = jquery__WEBPACK_IMPORTED_MODULE_0___default()('.block-content');
      $blockContentElements.each(function (index) {
        return new ContentBlocker(jquery__WEBPACK_IMPORTED_MODULE_0___default()($blockContentElements[index]));
      });
    }
  }, {
    key: "load",
    value: function load(groupdId) {
      var $blockContentElements = jquery__WEBPACK_IMPORTED_MODULE_0___default()('[data-consent-groupid="' + groupdId + '"].block-content');
      $blockContentElements.each(function (index) {
        ContentBlocker.loadContent(jquery__WEBPACK_IMPORTED_MODULE_0___default()($blockContentElements[index]));
      });
    }
  }, {
    key: "loadContent",
    value: function loadContent($element) {
      var type = $element.data("type");
      switch (type) {
        case "iframe":
          ContentBlocker.loadContentIFrame($element);
          break;
        case "userEvent":
          ContentBlocker.fireUserEvent($element);
          break;
      }
    }
  }, {
    key: "fireUserEvent",
    value: function fireUserEvent($element) {
      var event = new Event($element.data("event-name"));
      $element.html("");
      document.dispatchEvent(event);
    }
  }, {
    key: "loadContentIFrame",
    value: function loadContentIFrame($element) {
      $element.html('<iframe width="100%" height="100%" src="' + $element.data("src") + '"></iframe>');
    }
  }]);
}();


/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ ((module) => {

module.exports = jQuery;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
var __webpack_exports__ = {};
/*!***********************************************!*\
  !*** ../Resources/Private/JavaScript/Main.js ***!
  \***********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _ContentBlocker__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ContentBlocker */ "../Resources/Private/JavaScript/ContentBlocker.js");


jquery__WEBPACK_IMPORTED_MODULE_0___default()(function () {
  _ContentBlocker__WEBPACK_IMPORTED_MODULE_1__["default"].mount();
});
window.ContentBlocker = _ContentBlocker__WEBPACK_IMPORTED_MODULE_1__["default"];
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************************!*\
  !*** ../Resources/Private/Scss/main.scss ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

Main = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=Main.js.map