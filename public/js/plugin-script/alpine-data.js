/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!***************************************************!*\
  !*** ./resources/js/plugin-script/alpine-data.js ***!
  \***************************************************/
(function () {
  document.addEventListener('alpine:init', function () {
    Alpine.data('collapse', function (defaultValue) {
      return {
        selected: defaultValue,
        selectCollapse: function selectCollapse(content) {
          this.selected !== content ? this.selected = content : this.selected = null;
        },
        activeCollapse: function activeCollapse(ref, content, selected) {
          return selected == content ? "max-height: ".concat(ref[content].scrollHeight, "px") : '';
        }
      };
    });
  });
})();
/******/ })()
;