<?php

namespace Drupal\ad_entity;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;

/**
 * Class for collected targeting information.
 */
class TargetingCollection {

  /**
   * TargetingCollection constructor.
   *
   * @param array|string $info
   *   (Optional) Either an array or a JSON-encoded string
   *   holding initial targeting information.
   */
  public function __construct($info = NULL) {
    if (is_array($info)) {
      $this->collected = $info;
    }
    elseif (is_string($info)) {
      $this->collected = Json::decode($info);
    }
    else {
      $this->collected = [];
    }
  }

  /**
   * An array holding the collected targeting information.
   *
   * @var array
   */
  protected $collected;

  /**
   * Get the value for the given key.
   *
   * @param string $key
   *   The targeting key.
   *
   * @return string|array|null
   *   The targeting value.
   */
  public function get($key) {
    return !empty($this->collected[$key]) ? $this->collected[$key] : NULL;
  }

  /**
   * Sets the given key to the given value.
   *
   * @param string $key
   *   The targeting key.
   * @param string|array $value
   *   The targeting value.
   */
  public function set($key, $value) {
    $this->collected[$key] = $value;
  }

  /**
   * Adds the given key-value pair to the current collection.
   *
   * @param string $key
   *   The targeting key.
   * @param string|array $value
   *   The targeting value.
   */
  public function add($key, $value) {
    if (!empty($this->collected[$key])) {
      if (!is_array($this->collected[$key])) {
        $this->collected[$key] = [$this->collected[$key]];
      }
      if (is_array($value)) {
        $this->collected[$key] = array_merge($this->collected[$key], $value);
      }
      else {
        $this->collected[$key] = array_merge($this->collected[$key], [$value]);
      }
      $this->collected[$key] = array_unique($this->collected[$key]);
    }
    else {
      $this->collected[$key] = $value;
    }
  }

  /**
   * Removes the given key or key-value pair from the current collection.
   *
   * @param string $key
   *   The targeting key.
   * @param string $value
   *   (Optional) The targeting value to remove, if present.
   */
  public function remove($key, $value = NULL) {
    if (isset($value) && !empty($this->collected[$key])) {
      if (is_array($this->collected[$key])) {
        foreach ($this->collected[$key] as $index => $existing) {
          if ($value == $existing) {
            unset($this->collected[$key][$index]);
            if (empty($this->collected[$key])) {
              // There's no need for keys without values.
              unset($this->collected[$key]);
            }
          }
        }
      }
      else {
        if ($this->collected[$key] == $value) {
          unset($this->collected[$key]);
        }
      }
    }
    else {
      unset($this->collected[$key]);
    }
  }

  /**
   * Collects targeting info from the given user input.
   *
   * @param string $input
   *   The string which holds the user input.
   *   Keys with multiple values occur multiple times.
   *   Example format: "key1: value1, key2: value2, key2: value3".
   */
  public function collectFromUserInput($input) {
    $pairs = explode(',', Html::escape($input));
    foreach ($pairs as $pair) {
      $pair = explode(':', trim($pair));
      $count = count($pair);
      if ($count === 1) {
        $this->add('category', trim($pair[0]));
      }
      elseif ($count === 2) {
        $this->add(trim($pair[0]), trim($pair[1]));
      }
    }
  }

  /**
   * Collects targeting info from the given collection.
   *
   * @param \Drupal\ad_entity\TargetingCollection $collection
   *   The targeting collection to collect from.
   */
  public function collectFromCollection(TargetingCollection $collection) {
    foreach ($collection->toArray() as $key => $value) {
      $this->add($key, $value);
    }
  }

  /**
   * Collects targeting info from the given JSON string.
   *
   * @param string $json
   *   A JSON-encoded string which holds targeting information.
   */
  public function collectFromJson($json) {
    $this->collectFromCollection(new TargetingCollection($json));
  }

  /**
   * Whether the collection is empty or not.
   *
   * @return bool
   *   TRUE if the collection is empty, FALSE otherwise.
   */
  public function isEmpty() {
    return empty($this->collected);
  }

  /**
   * Returns the collected targeting information as an array.
   *
   * @return array
   *   The collection as array.
   */
  public function toArray() {
    return $this->collected;
  }

  /**
   * Returns the collected targeting information as a JSON-encoded string.
   *
   * @return string
   *   The collection as a JSON-encoded string.
   */
  public function toJson() {
    return json_encode($this->collected, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

  /**
   * Returns the collected targeting information as User-editable output.
   *
   * @return string
   *   The user-editable output.
   */
  public function toUserOutput() {
    $pairs = [];
    foreach ($this->collected as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $item) {
          $pairs[] = $key . ': ' . $item;
        }
      }
      else {
        $pairs[] = $key . ': ' . $value;
      }
    }
    return implode(', ', $pairs);
  }

}
