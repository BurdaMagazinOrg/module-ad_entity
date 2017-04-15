<?php

namespace Drupal\ad_entity\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation for Advertising view handler plugins.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class AdView extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the Advertising view handler.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The library which contains the JS implementation for this view handler.
   *
   * @var string
   */
  public $library;

  /**
   * A list of Advertising types the view handler is compatible with.
   *
   * @var string[]
   */
  public $allowedTypes;

}
