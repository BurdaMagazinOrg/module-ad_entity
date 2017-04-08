<?php

namespace Drupal\ad_entity\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation for Advertising context plugins.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class AdContext extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

}
