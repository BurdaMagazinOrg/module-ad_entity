/**
 * @file
 * Consent awareness for Advertising entities.
 */

// @todo This would not work for header.
//       Need a script including settings which can be run inside header,
//       but without the need of putting all Drupal and drupalSettings
//       into the header too. init_provider.js is run inside header and
//       would need this script.
(function (document, Drupal, settings) {

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.consent = {};

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

}(window.document, Drupal, drupalSettings));
