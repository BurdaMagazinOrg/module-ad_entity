/**
 * @file
 * JS for preparing the display of Advertising entities.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.adContainers = Drupal.ad_entity.adContainers || {};

  /**
   * Collects all Advertising entity containers from the given context.
   *
   * @param {object} context
   *   The part of the DOM being processed.
   */
  Drupal.ad_entity.collectAdContainers = function (context) {
    $('.ad-entity-container').each(function () {
      var id = $(this).attr('id');
      Drupal.ad_entity.adContainers[id] = $(this);
    });
  };

  /**
   * Filters out collected Advertising tags
   * which are not in the scope of the current device.
   */
  Drupal.ad_entity.restrictAdsToScope = function () {
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
    for (var id in Drupal.ad_entity.adContainers) {
      if (Drupal.ad_entity.adContainers.hasOwnProperty(id)) {
        var container = Drupal.ad_entity.adContainers[id];
        var container_device = container.attr('data-ad-entity-device');
        if (!($.inArray(container_device, to_remove) < 0)) {
          container.remove();
          delete Drupal.ad_entity.adContainers[id];
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
   * Drupal behavior for preparing the display of Advertising entities.
   */
  Drupal.behaviors.adEntityPrepare = {
    attach: function (context, settings) {
      Drupal.ad_entity.collectAdContainers(context);
      Drupal.ad_entity.restrictAdsToScope();

      // Let extensions run their own prepare implementations.
      if ('extensions' in Drupal.ad_entity) {
        for (var extension in Drupal.ad_entity.extensions) {
          if (Drupal.ad_entity.extensions.hasOwnProperty(extension)) {
            Drupal.ad_entity.extensions[extension].prepare(context, settings);
          }
        }
      }
    },
    detach: function (context, settings) {}
  };

}(jQuery, Drupal, window));
