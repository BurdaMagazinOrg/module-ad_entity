/**
 * @file
 * JS View handler implementation for ads which are using the 'dfp_default' view plugin.
 */

(function ($, Drupal, drupalSettings, window) {

  window.googletag = window.googletag || {};
  window.googletag.cmd = window.googletag.cmd || [];

  Drupal.ad_entity = Drupal.ad_entity || window.adEntity || {};

  Drupal.ad_entity.viewHandlers = Drupal.ad_entity.viewHandlers || {};

  var $window = $(window);

  Drupal.ad_entity.viewHandlers.dfp_default = {
    initialize: function (containers, context, settings) {
      window.googletag.cmd.push(function () {
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
        slot = window.googletag.defineOutOfPageSlot('/' + network_id + '/' + unit_id, ad_id);
      }
      else {
        var sizes = ad_tag.data('dfpSizes');
        if (typeof sizes !== 'object') {
          sizes = [];
        }
        slot = window.googletag.defineSlot('/' + network_id + '/' + unit_id, sizes, ad_id);
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
      if (this.withSlotOrder) {
        slot.setTargeting('slotNumber', slotNumber);
        slot.setTargeting('onPageLoad', onPageLoad);
      }

      slot.addService(window.googletag.pubads());
    },
    display: function (ad_tags) {
      // When possible, load multiple slots at once to support roadblocks.
      // Slots with the same ad unit path wouldn't be refreshed
      // more than one time though, thus they're being split up.
      var slots = [[]];
      var slots_length;
      var slots_list;
      var i;

      var ad_tags_length = ad_tags.length;
      for (i = 0; i < ad_tags_length; i++) {
        var ad_tag = ad_tags[i];
        var slot = ad_tag.data('slot');

        if (typeof slot === 'object') {
          window.googletag.display(ad_tag.data('id'));

          var unit_path = slot.getAdUnitPath();
          slots_length = slots.length;
          for (var j = 0; j < slots_length; j++) {
            var exists = false;
            slots_list = slots[j];
            var slots_list_length = slots_list.length;
            for (var k = 0; k < slots_list_length; k++) {
              if (unit_path === slots_list[k].getAdUnitPath()) {
                exists = true;
                break;
              }
            }
            if (exists === false) {
              slots_list.push(slot);
              break;
            }
            if ((j + 1) === slots.length) {
              slots.push([slot]);
            }
          }
        }
      }

      slots_length = slots.length;
      for (i = 0; i < slots_length; i++) {
        slots_list = slots[i];
        if (slots_list.length > 0) {
          window.googletag.pubads().refresh(slots_list);
        }
      }
    },
    addEventsFor: function (ad_tag, container) {
      // Mark container as initialized once advertisement has been loaded.
      var initHandler = function (event) {
        if (event.slot.getSlotElementId() === ad_tag.data('id')) {
          container.removeClass('not-initialized');
          container.addClass('initialized');
          container.data('initialized', true);
          if (event.isEmpty === true) {
            container.addClass('empty');
            container.removeClass('not-empty');
          }
          else {
            container.addClass('not-empty');
            container.removeClass('empty');
          }
          container.trigger('adEntity:initialized', [ad_tag, container]);
        }
      };

      window.googletag.pubads().addEventListener('slotRenderEnded', initHandler, false);
    },
    numberOfAds: 0,
    withSlotOrder: true
  };

  // Do not include slot order targeting when this feature is explicitly not enabled.
  if (drupalSettings.hasOwnProperty('dfp_order_info')) {
    if (!drupalSettings.dfp_order_info) {
      Drupal.ad_entity.viewHandlers.dfp_default.withSlotOrder = false;
    }
  }

}(jQuery, Drupal, drupalSettings, window));
