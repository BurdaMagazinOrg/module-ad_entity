/**
 * @file
 * Advertising Entity: Loader example for using the generic handler.
 *
 * Short description about the example: This implementation is assuming
 * to be loaded asynchronously. If it's a little late, it may check for
 * the queue, whether some tags are already waiting to be loaded.
 * Once done, it takes care of loading further ads on its own,
 * by adding itself as a loading handler.
 */

(function (adEntity) {

  var loadCallback = function (ad_tags) {
    // Should not use the logger on production.
    console.log('Load handler called. Items to load: ' + ad_tags.length);
    // Use the shift method to empty incoming queues.
    var ad_tag = ad_tags.shift();
    // Every ad_tag consists of the following properties:
    // - id: The DOM element ID as string.
    // - el: The DOM element as object itself.
    // - name: The internal machine name of the ad tag as string.
    // - format: The display format to use as string.
    // - targeting: Consists of common and custom / ad-specific targeting.
    //              Includes properties for numbered slots (slotNumber),
    //              whether the tag was added during page load (onPageLoad),
    //              and whether personalization is enabled (personalized).
    //   Tip: To get the current state of personalization, you can call
    //        window.adEntity.usePersonalization(), which returns true
    //        if personalized ads are allowed, and false otherwise.
    // - done(success, isEmpty): Once you finished loading the advertisement
    //   on this tag, this function should be called. The success parameter
    //   is a boolean indicating whether the ad loading was successful (true)
    //   or not (false). The isEmpty parameter is a boolean indicating whether
    //   the loading resulted in an empty slot (true) or not (false).
    // - isLoaded: Is a reserved flag indicating whether the tag was loaded or not.
    //             The flag will be automatically set by calling the done() function.
    // - isEmpty: Is a reserved flag indicating whether the loaded tag is empty or not.
    //            The flag will be automatically set by calling the done() function.
    // - data: Is a helper function to get and set data-* values.
    //         This is similar to jQuery's data callback.
    while (typeof ad_tag === 'object') {
      console.log('Loaded ad tag ' + ad_tag.name);
      // Important - call this function once loading is done:
      ad_tag.done(true, false);

      ad_tag = ad_tags.shift();
    }
  };
  var removeCallback = function (ad_tags) {
    // In case you need to do something when ads are being removed from the DOM,
    // you can do this withing this function.
    console.log('On-removal handler called. Items to remove: ' + ad_tags.length);
  };

  adEntity.generic = adEntity.generic || {toLoad: [], toRemove: [], loadHandlers: [], removeHandlers: []};

  // Removes the very first handler, which would only add further tags to the queue.
  adEntity.generic.loadHandlers.shift();
  adEntity.generic.loadHandlers.push({name: 'example_loader', callback: loadCallback});

  // Equivalent to the very first load handler, there is also one
  // which only adds tags to be removed from the queue.
  adEntity.generic.removeHandlers.shift();
  adEntity.generic.removeHandlers.push({name: 'example_on_removal', callback: removeCallback});

  // Directly use the callbacks as queue workers.
  loadCallback(adEntity.generic.toLoad);
  removeCallback(adEntity.generic.toRemove);

}(window.adEntity));
