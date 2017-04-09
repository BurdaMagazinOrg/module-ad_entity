<?php

namespace Drupal\ad_entity_adtech\Plugin\ad_entity\AdView;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdViewBase;

/**
 * Default view handler plugin for AdTech Factory advertisement.
 */
class AdTechView extends AdViewBase {

  /**
   * {@inheritdoc}
   */
  public function allowedTypes() {
    return ['adtech_factory'];
  }

  public function build(AdEntityInterface $entity) {
    return [];
  }

}