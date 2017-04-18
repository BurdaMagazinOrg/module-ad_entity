/**
 * @file
 * JS handler implementation for the 'adtech_targeting' context.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.context.adtech_targeting = {
    apply: function (container, context_settings, newcomers) {
      if (context_settings.hasOwnProperty('targeting')) {
        var context_targeting = context_settings.targeting;
        if (context_targeting) {
          var ad_tag = $('.adtech-factory-ad', container[0]);
          if (ad_tag.length === 0) {
            // This container doesn't hold an AdTech Factory tag.
            return;
          }
          var targeting = ad_tag.attr('data-adtech-targeting');
          if (targeting) {
            targeting = JSON.parse(targeting);
          }
          else {
            targeting = {};
          }

          // Merge the ad targeting with the given context targeting.
          for (var key in context_targeting) {
            if (context_targeting.hasOwnProperty(key)) {
              if (targeting.hasOwnProperty(key)) {
                if (targeting[key] === context_targeting[key]) {
                  continue;
                }
                if (!($.isArray(targeting[key]))) {
                  targeting[key] = [targeting[key]];
                }
                if (!($.isArray(context_targeting[key]))) {
                  context_targeting[key] = [context_targeting[key]];
                }
                for (var i = 0; i < context_targeting[key].length; i++) {
                  if ($.inArray(context_targeting[key][i], targeting[key]) < 0) {
                    targeting[key].push(context_targeting[key][i]);
                  }
                }
              }
              else {
                targeting[key] = context_targeting[key];
              }
            }
          }
          ad_tag.attr('data-adtech-targeting', JSON.stringify(targeting));
        }
      }
    }
  };

}(jQuery, Drupal, window));
