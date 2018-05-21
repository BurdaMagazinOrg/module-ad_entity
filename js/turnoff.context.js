/**
 * @file
 * JS handler implementation for the 'turnoff' context.
 */

(function ($, Drupal, window) {

  Drupal.ad_entity = Drupal.ad_entity || window.adEntity || {};

  Drupal.ad_entity.adContainers = Drupal.ad_entity.adContainers || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.context.turnoff = {
    apply: function (container, context_settings, newcomers) {
      var id = container.data('id');
      // Remove the container from the DOM.
      container.remove();
      // Delete the container from the global collection.
      delete Drupal.ad_entity.adContainers[id];
      // Delete the container from the current list.
      delete newcomers[id];
    }
  };

}(jQuery, Drupal, window));
