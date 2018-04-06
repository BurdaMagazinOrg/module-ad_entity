/**
 * @file
 * Tasks to run right after View handler building is complete.
 */

(function ($, ad_entity, behavior, settings, window) {

  'use strict';

  // Run attachment on first page load,
  // without waiting for other Drupal behaviors.
  if (!($.isEmptyObject(ad_entity.viewHandlers))) {
    behavior.attach(window.document, settings);
  }

}(jQuery, Drupal.ad_entity, Drupal.behaviors.adEntityView, drupalSettings, window));
