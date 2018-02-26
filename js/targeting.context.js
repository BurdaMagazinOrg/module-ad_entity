/**
 * @file
 * JS handler implementation for the 'targeting' context.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.context.targeting = {
    apply: function (container, context_settings, newcomers) {
      if (context_settings.hasOwnProperty('targeting')) {
        var context_targeting = context_settings.targeting;
        var targeting_element = $('[data-ad-entity-targeting]', container[0]);
        if (context_targeting && targeting_element.length > 0) {
          // Make sure to operate only on one targeting element.
          targeting_element = targeting_element.first();
          var targeting = targeting_element.data('adEntityTargeting');
          if (typeof targeting !== 'object') {
            targeting = {};
          }

          // Merge the container's current targeting with the given context targeting.
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
                var item_length = context_targeting[key].length;
                for (var i = 0; i < item_length; i++) {
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
          targeting_element.data('adEntityTargeting', targeting);
          targeting_element.attr('data-ad-entity-targeting', JSON.stringify(targeting));
        }
      }
    }
  };

}(jQuery, Drupal, window));
