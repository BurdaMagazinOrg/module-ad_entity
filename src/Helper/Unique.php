<?php

namespace Drupal\ad_entity\Helper;

use Drupal\Component\Utility\Html;

/**
 * Helper class for handling unique IDs.
 */
abstract class Unique {

  /**
   * An array of already seen IDs.
   *
   * @var array
   */
  protected static $seenIds = [];

  /**
   * Prepares a string for use as a valid HTML ID and guarantees uniqueness.
   *
   * For more details, see \Drupal\Component\Utility\Html::getUniqueId().
   *
   * @param string $id
   *   The ID to clean.
   *
   * @return string
   *   The cleaned ID.
   */
  static public function getHtmlId($id) {
    $id = Html::getUniqueId($id);

    if (isset(static::$seenIds[$id])) {
      // The Drupal component failed to guarantee uniqueness.
      // Now we need to take care of it on our own.
      return static::getHtmlId($id);
    }
    static::$seenIds[$id] = TRUE;

    return $id;
  }

}
