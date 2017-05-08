/**
 * @file
 * Fundamental JS implementation for viewing Advertising entities.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.adContainers = Drupal.ad_entity.adContainers || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  /**
   * Collects all Advertising containers from the given context.
   *
   * @param {object} context
   *   The part of the DOM being processed.
   *
   * @return {object}
   *   The newly added containers (newcomers).
   */
  Drupal.ad_entity.collectAdContainers = function (context) {
    var newcomers = {};
    $('.ad-entity-container', context).each(function () {
      var container = $(this);
      var id = container.attr('id');
      if (!(Drupal.ad_entity.adContainers.hasOwnProperty(id))) {
        Drupal.ad_entity.adContainers[id] = container;
        newcomers[id] = container;
      }
    });
    return newcomers;
  };

  /**
   * Filters out newly collected Advertising containers
   * which are not in the scope of the current breakpoint.
   *
   * @param {object} newcomers
   *   The list of newly collected containers to filter.
   */
  Drupal.ad_entity.restrictAdsToScope = function (newcomers) {
    var to_keep = ['any'];
    var breakpoint = window.themeBreakpoints.getCurrentBreakpoint();
    if (breakpoint) {
      to_keep.push(breakpoint.name);
    }

    for (var id in newcomers) {
      if (newcomers.hasOwnProperty(id)) {
        var in_scope = false;
        var container = newcomers[id];
        var variant = JSON.parse(container.attr('data-ad-entity-variant'));
        for (var i = 0; i < variant.length; i++) {
          if (!($.inArray(variant[i], to_keep) < 0)) {
            in_scope = true;
            break;
          }
        }
        if (!in_scope) {
          container.remove();
          delete Drupal.ad_entity.adContainers[id];
          delete newcomers[id];
        }
      }
    }
  };

  /**
   * Correlates the Advertising containers with their view handlers.
   *
   * @param {object} containers
   *   The list of Advertising containers to correlate.
   *
   * @return {object}
   *   The correlation.
   */
  Drupal.ad_entity.correlate = function (containers) {
    var correlation = {};
    var handler_id = '';
    for (var id in containers) {
      if (containers.hasOwnProperty(id)) {
        var container = containers[id];
        handler_id = container.attr('data-ad-entity-view');

        if (Drupal.ad_entity.viewHandlers.hasOwnProperty(handler_id)) {
          var view_handler = Drupal.ad_entity.viewHandlers[handler_id];
          correlation[handler_id] = correlation[handler_id] || {handler: view_handler, containers: {}};
          correlation[handler_id].containers[id] = container;
        }
      }
    }
    return correlation;
  };

  /**
   * Drupal behavior for viewing Advertising entities.
   */
  Drupal.behaviors.adEntityView = {
    attach: function (context, settings) {
      var containers = Drupal.ad_entity.collectAdContainers(context);
      Drupal.ad_entity.restrictAdsToScope(containers);
      var correlation = Drupal.ad_entity.correlate(containers);

      // Apply Advertising contexts, if available.
      if (!($.isEmptyObject(Drupal.ad_entity.context))) {
        Drupal.ad_entity.context.addFrom(context);
        Drupal.ad_entity.context.applyOn(containers);
      }

      // Let the view handlers act on attachment of their ads.
      for (var handler_id in Drupal.ad_entity.viewHandlers) {
        if (Drupal.ad_entity.viewHandlers.hasOwnProperty(handler_id)) {
          if (correlation.hasOwnProperty(handler_id)) {
            correlation[handler_id].handler.attach(correlation[handler_id].containers, context, settings);
          }
        }
      }
    },
    detach: function (context, settings) {

      var containers = {};
      $('.ad-entity-container', context).each(function () {
        var container = $(this);
        var id = container.attr('id');
        containers[id] = container;

        // Remove the detached container from the collection.
        delete Drupal.ad_entity.adContainers[id];
      });
      var correlation = Drupal.ad_entity.correlate(containers);

      // Let the view handlers act on detachment of their ads.
      for (var handler_id in Drupal.ad_entity.viewHandlers) {
        if (Drupal.ad_entity.viewHandlers.hasOwnProperty(handler_id)) {
          if (correlation.hasOwnProperty(handler_id)) {
            correlation[handler_id].handler.detach(correlation[handler_id].containers, context, settings);
          }
        }
      }
    }
  };

}(jQuery, Drupal, window));
