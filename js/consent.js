/**
 * @file
 * Consent awareness for Advertising entities.
 */

(function (document) {


  function getCookie(name) {
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

}(window.document));
