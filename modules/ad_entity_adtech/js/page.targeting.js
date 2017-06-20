/**
 * @file
 * JS implementation for adding page targeting.
 */

(function (drupalSettings, window) {

  'use strict';

  if (drupalSettings.hasOwnProperty('adtech_page_targeting')) {
    if (typeof window.atf_lib !== 'undefined') {
      var page_targeting = JSON.parse(drupalSettings['adtech_page_targeting']);
      for (var key in page_targeting) {
        if (page_targeting.hasOwnProperty(key)) {
          window.atf_lib.add_page_targeting(key, page_targeting[key]);
        }
      }
    }
  }

}(drupalSettings, window));
