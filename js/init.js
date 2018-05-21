/**
 * @file
 * Initialization script for Advertising entities.
 */

(function (window, document) {

  window.adEntity = window.adEntity || {};

  // @todo Theme Breakpoints JS should be a library too.

  window.adEntity.getCookie = function (name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    var i;
    var c;
    for(i=0; i < ca.length; i++) {
      c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
  }

}(window, window.document));
