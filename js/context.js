/**
 * @file
 * Fundamental JS implementation for applying Advertising context.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.contextObjects = Drupal.ad_entity.contextObjects || [];

  /**
   * Adds all context objects from the given DOM.
   *
   * @param {object} dom
   *   The DOM, usually provided by the Drupal context.
   */
  Drupal.ad_entity.context.addFrom = function (dom) {
    $('script[data-ad-entity-context]', dom).each(function () {
      var context_object = JSON.parse($(this).html());
      Drupal.ad_entity.contextObjects.push(context_object);
    });
  };

  /**
   * Applies all known context objects on the newly collected Advertising containers.
   *
   * @param {object} newcomers
   *   The list of newly collected Advertising containers.
   */
  Drupal.ad_entity.context.applyOn = function (newcomers) {
    var context_objects = Drupal.ad_entity.contextObjects;
    for (var i = 0; i < context_objects.length; i++) {
      var context_object = context_objects[i];
      for (var id in newcomers) {
        if (newcomers.hasOwnProperty(id)) {
          var container = newcomers[id];

          // Determine whether to apply the given context
          // on the Advertising container.
          var to_apply = true;
          if (context_object.hasOwnProperty('apply_on')) {
            var ad_entity_id = container.attr('data-ad-entity');
            if ($.inArray(ad_entity_id, context_object.apply_on) < 0) {
              to_apply = false;
            }
          }

          if (to_apply) {
            // When given, let the corresponding implementation
            // of the context plugin perform the appliance.
            var context_id = context_object.context_id;
            if (Drupal.ad_entity.context.hasOwnProperty(context_id)) {
              var context_settings = {};
              if (context_object.hasOwnProperty('settings')) {
                context_settings = context_object.settings;
              }
              Drupal.ad_entity.context[context_id].apply(container, context_settings, newcomers);
            }
          }
        }
      }
    }
  };

}(jQuery, Drupal, window));
