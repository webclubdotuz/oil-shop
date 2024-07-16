/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/plugin-script/alpine-store.js":
/*!****************************************************!*\
  !*** ./resources/js/plugin-script/alpine-store.js ***!
  \****************************************************/
/***/ (function() {

var _this = this;
document.addEventListener("alpine:init", function () {
  Alpine.store("main", {
    newMessage: "hello world",
    darkMode: true,
    isCompact: function isCompact() {
      if (localStorage.getItem('compactSidebar')) {
        return true;
      } else {
        return false;
      }
    },
    selected: null,
    // selectedCollapse: (value) => {
    //    this.selected = value;
    // },
    selectCollapse: function selectCollapse(value) {
      _this.selected !== value ? _this.selected = value : _this.selected = null;
      console.log(_this.selected);
    },
    activeCollapse: function activeCollapse(ref, content) {
      console.log(ref, content);
      return 'max-height: ' + ref["".concat(content)].scrollHeight + 'px';
    }
  });
});
console.log('I am from alpine js store');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/plugin-script/alpine-store.js"]();
/******/ 	
/******/ })()
;