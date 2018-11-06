/**
 * @file
 * Builds the base for loading and removing generic ads.
 */

(function (adEntity) {

  adEntity.generic = adEntity.generic || {toLoad: [], toRemove: []};
  adEntity.generic.load = adEntity.generic.load || function (ad_tags) {
    var i = 0;
    var length = this.loadHandlers.length;
    while (i < length) {
      this.loadHandlers[i].callback(ad_tags.slice());
      i++;
    }
  }.bind(adEntity.generic);
  adEntity.generic.remove = adEntity.generic.remove || function (ad_tags) {
    var i = 0;
    var length = this.removeHandlers.length;
    while (i < length) {
      this.removeHandlers[i].callback(ad_tags.slice());
      i++;
    }
  }.bind(adEntity.generic);
  adEntity.generic.loadHandlers = adEntity.generic.loadHandlers || [];
  adEntity.generic.loadHandlers.push({name: 'queue', callback: function (ad_tags) {
    var ad_tag = ad_tags.shift();
    this.toLoad = this.toLoad || [];
    while (typeof ad_tag === 'object') {
      this.toLoad.push(ad_tag);
      ad_tag = ad_tags.shift();
    }
  }.bind(adEntity.generic)});
  adEntity.generic.loadHandlers.push({name: 'done', callback: function (ad_tags) {
    var ad_tag = ad_tags.shift();
    while (typeof ad_tag === 'object') {
      ad_tag.done();
      ad_tag = ad_tags.shift();
    }
  }.bind(adEntity.generic)});
  adEntity.generic.removeHandlers = adEntity.generic.removeHandlers || [];
  adEntity.generic.removeHandlers.push({name: 'queue', callback: function (ad_tags) {
    var ad_tag = ad_tags.shift();
    this.toRemove = this.toRemove || [];
    while (typeof ad_tag === 'object') {
      this.toRemove.push(ad_tag);
      ad_tag = ad_tags.shift();
    }
  }.bind(adEntity.generic)});

}(window.adEntity));
