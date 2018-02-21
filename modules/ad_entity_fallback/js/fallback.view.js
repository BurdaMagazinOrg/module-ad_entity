/**
 * @file
 * JS fallback view handler implementation.
 */

(function ($, Drupal, window) {

  'use strict';

  var $window = $(window);

  Drupal.ad_entity.fallbacks = Drupal.ad_entity.fallbacks || {};

  var fallbacks = Drupal.ad_entity.fallbacks;

  /**
   * Correlates all known ad containers with their fallback containers.
   *
   * @param {object} containers
   *   The list of containers with both original and fallback containers.
   *
   * @return {object}
   *   The correlation.
   */
  fallbacks.correlateContainers = function (containers) {
    var id;
    var correlationId;
    var container;
    var correlated = {};
    var item;
    for (id in containers) {
      if (containers.hasOwnProperty(id)) {
        container = containers[id];
        // Fetch the original container.
        correlationId = container.data('fallbackContainer');
        if (typeof correlationId !== 'undefined') {
          if (typeof correlated[correlationId] === 'undefined') {
            correlated[correlationId] = { originalContainer: null, fallbackContainer: null };
          }
          correlated[correlationId].originalContainer = container;
        }
        else {
          // Fetch the fallback container.
          correlationId = container.data('fallbackContainerFor');
          if (typeof correlationId !== 'undefined') {
            if (typeof correlated[correlationId] === 'undefined') {
              correlated[correlationId] = { originalContainer: null, fallbackContainer: null };
            }
            correlated[correlationId].fallbackContainer = container;
          }
        }
        if (typeof correlationId !== 'undefined') {
          item = correlated[correlationId];
          // Create a reference to the instance of the fallback container.
          if (item.originalContainer !== null && item.fallbackContainer !== null) {
            item.originalContainer.data('fallbackObject', item.fallbackContainer);
          }
        }
      }
    }
    return correlated;
  };

  /**
   * Loads fallback containers in case the original ones are empty.
   *
   * @param {object} containers
   *   The list of containers with both original and fallback containers.
   * @param {object} context
   *   The DOM context.
   * @param {object} settings
   *   The Drupal settings.
   */
  fallbacks.processFallbacks = function (containers, context, settings) {
    var correlated = this.correlateContainers(containers);
    var to_load = {};
    for (var correlationId in correlated) {
      if (correlated.hasOwnProperty(correlationId)) {
        var item = correlated[correlationId];
        var original = item.originalContainer;
        var fallback = item.fallbackContainer;
        if (original === null || fallback === null) {
          continue;
        }
        if (original.data('initialized') === true || original.data('inScope') !== true || original.data('fallbackProcessed') === true) {
          continue;
        }
        if (fallback.data('initialized') === true || fallback.data('inScope') !== true) {
          continue;
        }
        fallback.removeClass('initialization-disabled');
        fallback.data('disabled', false);
        var id = fallback.data('id');
        to_load[id] = fallback;

        // Make sure that others won't try to
        // initialize the original container again.
        original.addClass('initialization-disabled');
        original.data('disabled', true);
        original.data('fallbackProcessed', true);
        original.remove();
      }
    }
    if (Object.keys(to_load).length > 0) {
      Drupal.ad_entity.restrictAndInitialize(to_load, context, settings);
    }
  };

  /**
   * Event listener callback when Advertising containers have been collected.
   *
   * @param {object} event
   *   The corresponding event object.
   * @param {object} all_containers
   *   The global collection of containers.
   * @param {object} newcomers
   *   The newly collected containers.
   * @param {object} context
   *   The DOM context.
   * @param {object} settings
   *   The Drupal settings.
   */
  fallbacks.onCollect = function (event, all_containers, newcomers, context, settings) {
    var processCallback = this.processFallbacks.bind(this, newcomers, context, settings);
    // @todo Make defer-time configurable.
    window.setTimeout(processCallback, 1000);
  };

  /**
   * Event listener callback when an Advertising container has been initialized.
   *
   * @param {object} event
   *   The corresponding event object.
   * @param {object} ad_tag
   *   The affected ad tag inside the Advertising container.
   * @param {object} container
   *   The Advertising container which has been initialized.
   */
  fallbacks.onInitialized = function (event, ad_tag, container) {
    var correlationId = container.data('fallbackContainer');
    if (typeof correlationId === 'undefined') {
      return;
    }
    var fallback = container.data('fallbackObject');
    if (typeof fallback === 'undefined') {
      // Perform a complete lookup on all containers
      // to fetch the corresponding fallback container.
      var all_containers = Drupal.ad_entity.adContainers;
      for (var id in all_containers) {
        if (all_containers.hasOwnProperty(id)) {
          var suspect = all_containers[id];
          if (correlationId === suspect.data('fallbackContainerFor')) {
            fallback = suspect;
            break;
          }
        }
      }
    }
    if (typeof fallback === 'undefined') {
      return;
    }
    container.data('fallbackProcessed', true);
    fallback.remove();
  };

  $window.on('adEntity:collected', fallbacks.onCollect.bind(fallbacks));
  $window.on('adEntity:initialized', fallbacks.onInitialized.bind(fallbacks));

}(jQuery, Drupal, window));
