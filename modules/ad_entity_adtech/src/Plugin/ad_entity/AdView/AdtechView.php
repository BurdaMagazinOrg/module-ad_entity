<?php

namespace Drupal\ad_entity_adtech\Plugin\ad_entity\AdView;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdViewBase;

/**
 * Default view handler plugin for AdTech Factory advertisement.
 *
 * @AdView(
 *   id = "adtech_default",
 *   label = "Default view for an AdTech Factory tag"
 * )
 */
class AdtechView extends AdViewBase {

  /**
   * {@inheritdoc}
   */
  public function allowedTypes() {
    return ['adtech_factory'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(AdEntityInterface $entity) {
    return [
      '#theme' => 'adtech_default',
      '#ad_entity' => $entity,
    ];
  }

}
