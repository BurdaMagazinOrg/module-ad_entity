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
    $('.ad-entity-container', context).each(function () {
      var container = $(this);
      if (container.hasClass('not-initialized')) {
        var id = container.attr('id');
        if (Drupal.ad_entity.adContainers.hasOwnProperty(id)) {
          // Guarantee uniqueness of the container and its children.
          var length = Object.keys(Drupal.ad_entity.adContainers).length;
          id = id + '-' + length;
          container.attr('id', id);
          $('[id]', container[0]).each(function () {
            var $this = $(this);
            var new_id = $this.attr('id') + '-' + length;
            $this.attr('id', new_id);
          });
        }
        Drupal.ad_entity.adContainers[id] = container;
        newcomers[id] = container;
        container.trigger('adEntity:collected', [Drupal.ad_entity.adContainers, newcomers, context, settings]);
      }
    });
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
        container.variant = container.variant || JSON.parse(container.attr('data-ad-entity-variant'));
        for (var i = 0; i < container.variant.length; i++) {
          if (!($.inArray(container.variant[i], scope) < 0)) {
            in_scope[id] = container;
            if (!container.hasClass('in-scope')) {
              container.addClass('in-scope');
              container.removeClass('out-of-scope');
              container.css('display', '');
            }
            break;
          }
        }
        if (!in_scope.hasOwnProperty(id) && !container.hasClass('out-of-scope')) {
          container.removeClass('in-scope');
          container.addClass('out-of-scope');
          container.css('display', 'none');
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
        // Do not initialize disabled containers.
        if (to_initialize[id].hasClass('initialization-disabled')) {
          delete to_initialize[id];
        }
        // Prevent re-initialization of already initialized Advertisement.
        else if (to_initialize[id].hasClass('initialized') || !to_initialize[id].hasClass('not-initialized')) {
          delete to_initialize[id];
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
      if (settings.hasOwnProperty('ad_entity_responsive')) {
        if (settings.ad_entity_responsive === true) {
          $(window).on('themeBreakpoint:changed', function () {
            Drupal.ad_entity.restrictAndInitialize(containers, context, settings);
          });
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
