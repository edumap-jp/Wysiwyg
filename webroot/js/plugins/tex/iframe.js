/**
 * tex.iframe.js
 * iframe内でのtexを使うためのscript
 */
var TEX_IFR = new (function TEX_IFR() {
  var self = this;
  var $IFRAME;

  var __setMathJax = function(id) {
    MathJax.typesetPromise();
  };
  self.init = function($ifr) {
    $IFRAME = $ifr;
    // init MathJax
    MathJax = {
      tex: { inlineMath: [['$$', '$$'], ['\\(', '\\)']] }
    };
    __setMathJax();
  };
})();
