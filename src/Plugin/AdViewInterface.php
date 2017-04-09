<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\ad_entity\Entity\AdEntityInterface;

/**
 * Defines the plugin interface for Advertising view handlers.
 */
interface AdViewInterface extends PluginInspectionInterface {

  /**
   * Returns a list of Advertising types the view is compatible with.
   *
   * @return array
   *   An array containing the type plugin Ids as values.
   */
  public function allowedTypes();

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
