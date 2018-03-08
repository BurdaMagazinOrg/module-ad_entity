/**
 * @file
 * JS View handler implementation for ads which are using the 'dfp_default' view plugin.
 */

(function ($, Drupal, window) {

  'use strict';

  if (typeof window.googletag === 'undefined') {
    window.googletag = {};
  }
  var googletag = window.googletag;
  googletag.cmd = googletag.cmd || [];

  var $window = $(window);

  Drupal.ad_entity = Drupal.ad_entity || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  Drupal.ad_entity.viewHandlers.dfp_default = Drupal.ad_entity.viewHandlers.dfp_default || {
    initialize: function (containers, context, settings) {
      googletag.cmd.push(function () {
        var onPageLoad = 'true';
        if (this.numberOfAds > 0) {
          onPageLoad = 'false';
        }
        var ad_tags = [];
        for (var id in containers) {
          if (containers.hasOwnProperty(id)) {
            this.numberOfAds++;
            var container = containers[id];
            var ad_tag = $('.google-dfp-ad', container[0]);
            ad_tag.data('id', ad_tag.attr('id'));
            this.define(ad_tag, this.numberOfAds.toString(), onPageLoad, container);
            this.addEventsFor(ad_tag, container);
            ad_tags.push(ad_tag);
          }
        }
        this.display(ad_tags);
      }.bind(this));
    },
    detach: function (containers, context, settings) {},
    define: function (ad_tag, slotNumber, onPageLoad, container) {
      var ad_id = ad_tag.data('id');
      var network_id = ad_tag.data('dfpNetwork');
      var unit_id = ad_tag.data('dfpUnit');
      var out_of_page = ad_tag.data('dfpOutOfPage');

      var slot;
      if (out_of_page === true) {
        slot = googletag.defineOutOfPageSlot('/' + network_id + '/' + unit_id, ad_id);
      }
      else {
        var sizes = ad_tag.data('dfpSizes');
        if (typeof sizes !== 'object') {
          sizes = [];
        }
        slot = googletag.defineSlot('/' + network_id + '/' + unit_id, sizes, ad_id);
      }

      ad_tag.data('slot', slot);

      var targeting = container.data('adEntityTargeting');
      if (typeof targeting !== 'object') {
        targeting = {};
      }

      $window.trigger('dfp:BeforeDisplay', [slot, targeting, slotNumber, onPageLoad]);

      for (var key in targeting) {
        if (targeting.hasOwnProperty(key)) {
          slot.setTargeting(key, targeting[key]);
        }
      }
      slot.setTargeting('slotNumber', slotNumber);
      slot.setTargeting('onPageLoad', onPageLoad);

      slot.addService(googletag.pubads());
    },
    display: function (ad_tags) {
      var slots = [];
      for (var i in ad_tags) {
        if (ad_tags.hasOwnProperty(i)) {
          var ad_tag = ad_tags[i];
          var slot = ad_tag.data('slot');
          if (typeof slot === 'object') {
            googletag.display(ad_tag[0]);
            slots.push(slot);
          }
        }
      }
      if (slots.length > 0) {
        googletag.pubads().refresh(slots);
      }
    },
    addEventsFor: function (ad_tag, container) {
      // Mark container as initialized once advertisement has been loaded.
      googletag.pubads().addEventListener('slotRenderEnded', function (event) {
        if (event.slot.getSlotElementId() === ad_tag.data('id')) {
          container.removeClass('not-initialized');
          container.addClass('initialized');
          container.data('initialized', true);
          container.trigger('adEntity:initialized', [ad_tag, container]);
        }
      }, false);
    },
    numberOfAds: 0
  };

}(jQuery, Drupal, window));
