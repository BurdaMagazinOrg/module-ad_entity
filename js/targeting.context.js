/**
 * @file
 * JS handler implementation for the 'targeting' context.
 */

(function ($, Drupal, window) {

  Drupal.ad_entity = Drupal.ad_entity || window.adEntity || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.context.targeting = {
    apply: function (container, context_settings, newcomers) {
      if (context_settings.hasOwnProperty('targeting')) {
        var context_targeting = context_settings.targeting;
        if (typeof context_targeting === 'object') {
          var targeting = container.data('adEntityTargeting');
          if (typeof targeting !== 'object') {
            targeting = {};
          }

          this.merge(targeting, context_targeting);

          container.data('adEntityTargeting', targeting);
          container.attr('data-ad-entity-targeting', JSON.stringify(targeting));
        }
      }
    },
    merge: function (targeting, context_targeting) {
      // Merge the targeting with the given context targeting.
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
    }
  };

}(jQuery, Drupal, window));
