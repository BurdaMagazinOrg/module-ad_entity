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

}
