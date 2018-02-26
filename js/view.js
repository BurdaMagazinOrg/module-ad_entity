/**
 * @file
 * Fundamental JS implementation for viewing Advertising entities.
 */

(function ($, Drupal, window) {

  'use strict';

  var $window = $(window);

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.adContainers = Drupal.ad_entity.adContainers || {};

  Drupal.ad_entity.context = Drupal.ad_entity.context || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  /**
   * Collects all not yet initialized Advertising containers from the given context.
   *
   * @param {object} context
   *   The part of the DOM being processed.
   * @param {object} settings
   *   The Drupal settings.
   *
   * @return {object}
   *   The newly added containers (newcomers).
   */
  Drupal.ad_entity.collectAdContainers = function (context, settings) {
    var newcomers = {};
    var collected = Drupal.ad_entity.adContainers;
    $('.ad-entity-container', context).each(function () {
      var id = this.id;
      if (typeof id !== 'string' || !(id.length > 0)) {
        return;
      }
      if (collected.hasOwnProperty(id)) {
        return;
      }
      var container = $(this);
      collected[id] = container;
      newcomers[id] = container;
      container.data('id', id);
    });
    $window.trigger('adEntity:collected', [collected, newcomers, context, settings]);
    return newcomers;
  };

  /**
   * Restricts the given list of Advertising containers
   * to the scope of the current breakpoint.
   *
   * @param {object} containers
   *   The list of Advertising containers to restrict.
   *
   * @return {object}
   *   The containers which are in the scope of the current breakpoint.
   */
  Drupal.ad_entity.restrictAdsToScope = function (containers) {
    var scope = ['any'];
    if (typeof window.themeBreakpoints.getCurrentBreakpoint === 'function') {
      var breakpoint = window.themeBreakpoints.getCurrentBreakpoint();
      if (breakpoint) {
        scope.push(breakpoint.name);
      }
    }

    var in_scope = {};
    for (var id in containers) {
      if (containers.hasOwnProperty(id)) {
        var container = containers[id];
        var variant = container.data('adEntityVariant');
        var variant_length = variant.length;
        for (var i = 0; i < variant_length; i++) {
          if (!($.inArray(variant[i], scope) < 0)) {
            in_scope[id] = container;
            if (container.data('inScope') !== true) {
              container.addClass('in-scope');
              container.removeClass('out-of-scope');
              container.css('display', '');
              container.data('inScope', true);
            }
            break;
          }
        }
        if (!in_scope.hasOwnProperty(id) && container.data('inScope') !== false) {
          container.removeClass('in-scope');
          container.addClass('out-of-scope');
          container.css('display', 'none');
          container.data('inScope', false);
        }
      }
    }

    return in_scope;
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
        handler_id = container.data('adEntityView');

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
   * Applies scope restriction and proper initialization
   * on given Advertisement containers.
   *
   * @param {object} containers
   *   The list of Advertising containers to restrict and initialize.
   * @param {object} context
   *   The DOM context.
   * @param {object} settings
   *   The Drupal settings.
   */
  Drupal.ad_entity.restrictAndInitialize = function (containers, context, settings) {
    var to_initialize = Drupal.ad_entity.restrictAdsToScope(containers);

    for (var id in to_initialize) {
      if (to_initialize.hasOwnProperty(id)) {
        var container = to_initialize[id];
        var initialized = container.data('initialized');
        if (typeof initialized !== 'boolean') {
          initialized = !container.hasClass('not-initialized');
          container.data('initialized', initialized);
        }
        // Prevent re-initialization of already initialized Advertisement.
        if (initialized === true) {
          delete to_initialize[id];
        }
        else {
          // Do not initialize disabled containers.
          // As per documentation since beta status,
          // the primary flag for disabling initialization
          // is the class name.
          var disabled = container.hasClass('initialization-disabled');
          container.data('disabled', disabled);
          if (disabled) {
            delete to_initialize[id];
          }
        }
      }
    }

    // Let the view handlers initialize their ads.
    var correlation = Drupal.ad_entity.correlate(to_initialize);
    for (var handler_id in Drupal.ad_entity.viewHandlers) {
      if (Drupal.ad_entity.viewHandlers.hasOwnProperty(handler_id)) {
        if (correlation.hasOwnProperty(handler_id)) {
          correlation[handler_id].handler.initialize(correlation[handler_id].containers, context, settings);
        }
      }
    }
  };

  /**
   * Drupal behavior for viewing Advertising entities.
   */
  Drupal.behaviors.adEntityView = {
    attach: function (context, settings) {
      var containers = Drupal.ad_entity.collectAdContainers(context, settings);

      // Apply Advertising contexts, if available.
      if (!($.isEmptyObject(Drupal.ad_entity.context))) {
        Drupal.ad_entity.context.addFrom(context);
        Drupal.ad_entity.context.applyOn(containers);
      }

      // Apply initial scope restriction and initialization on given Advertisement.
      Drupal.ad_entity.restrictAndInitialize(containers, context, settings);

      // When responsive behavior is enabled,
      // re-apply scope restriction with initialization on breakpoint changes.
      if (settings.hasOwnProperty('ad_entity') && settings.ad_entity.hasOwnProperty('responsive')) {
        if (settings.ad_entity.responsive === true) {
          $window.on('themeBreakpoint:changed', function () {
            Drupal.ad_entity.restrictAndInitialize(containers, context, settings);
          });
        }
      }
    },
    detach: function (context, settings) {

      var containers = {};
      var collected = Drupal.ad_entity.adContainers;

      // Remove the detached container from the collection,
      // but keep them in mind for other view handlers to act on.
      $('.ad-entity-container', context).each(function () {
        var id = this.id;
        if (typeof id !== 'string' || !(id.length > 0)) {
          return;
        }
        if (!collected.hasOwnProperty(id)) {
          return;
        }

        containers[id] = collected[id];
        delete collected[id];
      });

      // Let the view handlers act on detachment of their ads.
      var correlation = Drupal.ad_entity.correlate(containers);
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
