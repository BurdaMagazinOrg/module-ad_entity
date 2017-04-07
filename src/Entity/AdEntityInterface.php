<?php

namespace Drupal\ad_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Advertising entities.
 */
interface AdEntityInterface extends ConfigEntityInterface {

  /**
   * Get the corresponding Advertisement type plugin.
   *
   * @return \Drupal\ad_entity\Plugin\AdTypeInterface
   *   An instance of the Advertisement type plugin.
   */
  public function getTypePlugin();

  /**
   * Get the corresponding Advertisement view handler plugin.
   *
   * @return \Drupal\ad_entity\Plugin\AdViewInterface
   *   An instance of the Advertisement view plugin handler.
   */
  public function getViewPlugin();

}
