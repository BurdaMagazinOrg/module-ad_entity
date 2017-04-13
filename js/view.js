/**
 * @file
 * JS implementation for viewing Advertising entities.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.adContainers = Drupal.ad_entity.adContainers || {};

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
   * which are not in the scope of the current device.
   *
   * @param {object} newcomers
   *   The list of newly collected containers to filter.
   */
  Drupal.ad_entity.restrictAdsToScope = function (newcomers) {
    var client_device = Drupal.ad_entity.currentDeviceType();
    var to_remove = [];
    switch (client_device) {
      case 'smartphone':
        to_remove = ['tablet', 'desktop'];
        break;
      case 'tablet':
        to_remove = ['smartphone', 'desktop'];
        break;
      case 'desktop':
        to_remove = ['smartphone', 'tablet'];
        break;
    }
    for (var id in newcomers) {
      if (newcomers.hasOwnProperty(id)) {
        var container = newcomers[id];
        var container_device = container.attr('data-ad-entity-device');
        if (!($.inArray(container_device, to_remove) < 0)) {
          container.remove();
          delete Drupal.ad_entity.adContainers[id];
          delete newcomers[id];
        }
      }
    }
  };

  /**
   * Detects the currently used type of client device,
   * based on the information provided by the Breakpoint JS settings module.
   *
   * @return {string}
   *   The detected client device type.
   */
  Drupal.ad_entity.currentDeviceType = function () {
    var Breakpoints = window.breakpointSettings.Breakpoints;
    var DeviceMapping = window.breakpointSettings.DeviceMapping;

    if (window.innerWidth < Breakpoints[DeviceMapping.tablet]) {
      return 'smartphone';
    }
    if (window.innerWidth < Breakpoints[DeviceMapping.desktop]) {
      return 'tablet';
    }
    return 'desktop';
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
          correlation[handler_id][containers][id] = container;
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
