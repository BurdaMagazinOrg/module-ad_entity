<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\ad_entity\Entity\AdEntityInterface;

/**
 * Defines the plugin interface for Advertising view handlers.
 */
interface AdViewInterface extends PluginInspectionInterface {

  /**
   * Builds a renderable array for viewing the given Advertising entity.
   *
   * @param \Drupal\ad_entity\Entity\AdEntityInterface $entity
   *   The Advertising entity being viewed.
   *
   * @return array
   *   The view as a render array.
   */
  public function build(AdEntityInterface $entity);

}
