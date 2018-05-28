/**
 * @file
 * JS implementation for adding page targeting.
 */

(function (adEntity, settings, window) {

  adEntity.adtechAddPageTargeting = function (settings) {
    var page_targeting;
    var key;
    var delay;

    if (settings.hasOwnProperty('adtech_page_targeting')) {
      this.adtechLoadingAttempts = true;
      if (typeof window.atf_lib !== 'undefined') {
        page_targeting = JSON.parse(settings['adtech_page_targeting']);
        for (key in page_targeting) {
          if (page_targeting.hasOwnProperty(key)) {
            window.atf_lib.add_page_targeting(key, page_targeting[key]);
          }
        }
      }
      else {
        if (typeof this.adtechLoadingAttempts === 'undefined') {
          this.adtechLoadingAttempts = 0;
          this.adtechLoadingUnit = 'page_targeting';
        }
        if (this.adtechLoadingAttempts === false) {
          // Failed to load the library entirely, abort.
          return;
        }
        if (typeof this.adtechLoadingAttempts === 'number') {
          if (this.adtechLoadingAttempts < 40) {
            this.adtechLoadingAttempts++;
            delay = 10 * this.adtechLoadingAttempts;
            if (!(this.adtechLoadingUnit === 'page_targeting')) {
              // Another unit is already trying to load the library.
              // Add further delay to ensure this one is being fired later.
              delay += 100;
            }
            window.setTimeout(this.adtechAddPageTargeting.bind(this), delay, settings);
          }
          else {
            this.adtechLoadingAttempts = false;
          }
        }
      }
    }
  };

  adEntity.adtechAddPageTargeting(settings);

}(window.adEntity, drupalSettings, window));
