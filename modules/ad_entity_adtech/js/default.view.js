/**
 * @file
 * JS View handler implementation for ads which are using the 'adtech_default' view plugin.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  Drupal.ad_entity.viewHandlers.adtech_default = Drupal.ad_entity.viewHandlers.adtech_default || {
    attach: function (containers, context, settings) {
      if (typeof window.atf_lib !== 'undefined') {
        var load_arguments = [];
        var onPageLoad = true;
        if (this.numberOfAds > 0) {
          onPageLoad = false;
        }
        for (var id in containers) {
          if (containers.hasOwnProperty(id)) {
            this.numberOfAds++;
            var container = containers[id];
            var ad_tag = $('.adtech-factory-ad', container[0]);
            var argument = {element: ad_tag[0]};
            var targeting = ad_tag.attr('data-ad-entity-targeting');
            if (targeting) {
              argument.targeting = JSON.parse(targeting);
            }
            else {
              argument.targeting = {};
            }
            argument.targeting.slotNumber = this.numberOfAds;
            argument.targeting.onPageLoad = onPageLoad;
            load_arguments.push(argument);
          }
        }
        window.atf_lib.load_tag(load_arguments);
      }
    },
    detach: function (containers, context, settings) {},
    numberOfAds: 0
  };

}(jQuery, Drupal, window));
