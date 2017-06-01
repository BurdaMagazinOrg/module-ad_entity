/**
 * @file
 * JS View handler implementation for ads which are using the 'dfp_default' view plugin.
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  var googletag = window.googletag || {};
  googletag.cmd = googletag.cmd || [];

  var $window = $window || $(window);

  Drupal.ad_entity.viewHandlers.dfp_default = Drupal.ad_entity.viewHandlers.dfp_default || {
    initialize: function (containers, context, settings) {
      var onPageLoad = 'true';
      if (this.numberOfAds > 0) {
        onPageLoad = 'false';
      }
      for (var id in containers) {
        if (containers.hasOwnProperty(id)) {
          this.numberOfAds++;
          var container = containers[id];
          var ad_tag = $('.google-dfp-ad', container[0]);
          this.defineAndDisplay(ad_tag, this.numberOfAds.toString(), onPageLoad);
          this.addEventsFor(ad_tag, container);
        }
      }
    },
    detach: function (containers, context, settings) {},
    defineAndDisplay: function (ad_tag, slotNumber, onPageLoad) {
      googletag.cmd.push(function () {
        var ad_id = ad_tag.attr('id');
        var network_id = ad_tag.attr('data-dfp-network');
        var unit_id = ad_tag.attr('data-dfp-unit');
        var sizes = ad_tag.attr('data-dfp-sizes');
        if (sizes) {
          sizes = JSON.parse(sizes);
        }
        else {
          sizes = [];
        }

        var slot = googletag.defineSlot('/' + network_id + '/' + unit_id, sizes, ad_id);
        var targeting = ad_tag.attr('data-ad-entity-targeting');

        $window.trigger('dfp:BeforeDisplay', [slot, targeting, slotNumber, onPageLoad]);

        if (targeting) {
          targeting = JSON.parse(targeting);
          for (var key in targeting) {
            if (targeting.hasOwnProperty(key)) {
              slot.setTargeting(key, targeting[key]);
            }
          }
        }
        slot.setTargeting('slotNumber', slotNumber);
        slot.setTargeting('onPageLoad', onPageLoad);

        slot.addService(googletag.pubads());
        googletag.display(ad_id);
        googletag.pubads().refresh([slot]);
      });
    },
    addEventsFor: function (ad_tag, container) {
      googletag.cmd.push(function () {
        // Mark container as initialized once advertisement has been loaded.
        googletag.pubads().addEventListener('slotRenderEnded', function (event) {
          if (event.slot.getSlotElementId() === ad_tag.attr('id')) {
            container.removeClass('not-initialized');
            container.addClass('initialized');
          }
        }, false);
      });
    },
    numberOfAds: 0
  };

}(jQuery, Drupal, window));
