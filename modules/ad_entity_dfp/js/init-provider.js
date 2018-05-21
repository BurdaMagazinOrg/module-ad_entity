/**
 * @file
 * Initializes the DFP provider.
 */

var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];

googletag.cmd.push(function () {
  googletag.pubads().enableSingleRequest(true);
  googletag.pubads().disableInitialLoad();
  googletag.pubads().collapseEmptyDivs();
  googletag.enableServices();
});
