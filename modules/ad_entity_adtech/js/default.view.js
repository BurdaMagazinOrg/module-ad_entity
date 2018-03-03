/**
 * @file
 * JS View handler implementation for ads which are using the 'adtech_default' view plugin.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  var $window = $(window);

  Drupal.ad_entity.viewHandlers.adtech_default = Drupal.ad_entity.viewHandlers.adtech_default || {
    initialize: function (containers, context, settings) {
      if (typeof window.atf_lib !== 'undefined') {
        var load_arguments = [];
        var onPageLoad = true;
        if (this.numberOfAds > 0) {
          onPageLoad = false;
        }
        for (var id in containers) {
          if (containers.hasOwnProperty(id)) {
            this.numberOfAds++;
            var container = containers[id];
            var ad_tag = $('.adtech-factory-ad', container[0]);
            var argument = {element: ad_tag[0]};
            var targeting = container.data('adEntityTargeting');
            if (typeof targeting === 'object') {
              argument.targeting = targeting;
            }
            else {
              argument.targeting = {};
            }
            argument.targeting.slotNumber = this.numberOfAds;
            argument.targeting.onPageLoad = onPageLoad;
            load_arguments.push(argument);
            this.addEventsFor(ad_tag, container);
          }
        }
        $window.trigger('atf:BeforeLoad', [load_arguments, onPageLoad]);
        window.atf_lib.load_tag(load_arguments);
      }
      else {
        if (this.numberOfLoadingAttempts < 20) {
          this.numberOfLoadingAttempts++;
          var delay = 10 * this.numberOfLoadingAttempts;
          window.setTimeout(this.initialize.bind(this), delay, containers, context, settings);
        }
      }
    },
    detach: function (containers, context, settings) {},
    addEventsFor: function (ad_tag, container) {
      // Mark container as initialized once advertisement has been loaded.
      window.addEventListener('atf_ad_rendered', function (event) {
        if (event.element_id === ad_tag.attr('id')) {
          container.removeClass('not-initialized');
          container.addClass('initialized');
          container.data('initialized', true);
          container.trigger('adEntity:initialized', [ad_tag, container]);
        }
      }, false);
    },
    numberOfAds: 0,
    numberOfLoadingAttempts: 0
  };

}(jQuery, Drupal, window));
