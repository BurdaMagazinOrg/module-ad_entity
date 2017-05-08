<?php

namespace Drupal\ad_entity;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the base view builder for Advertising entities.
 */
class AdEntityViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {}

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = '["any"]', $langcode = NULL) {
    $build = $this->viewMultiple([$entity], $view_mode, $langcode);
    return !empty($build) ? reset($build) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = '["any"]', $langcode = NULL) {
    if ($view_mode == 'default' || $view_mode == 'full') {
      $view_mode = '["any"]';
    }
    if (strpos($view_mode, '["') !== 0) {
      $view_mode = '["' . $view_mode . '"]';
    }

    /** @var \Drupal\ad_entity\Entity\AdEntityInterface[] $entities */
    $build = [];

    foreach ($entities as $entity) {
      // Check whether a given context wants to turn off the advertisement.
      $turnoff = $entity->getContextDataForPlugin('turnoff');
      if (!empty($turnoff)) {
        continue;
      }

      // Build the view. No caching is defined here,
      // because there might be multiple blocks on one page
      // using the same advertising entity.
      // Advertising blocks will be cached anyway.
      $build[$entity->id()] = [
        '#theme' => 'ad_entity',
        '#ad_entity' => $entity,
        '#variant' => $view_mode,
      ];
    }

    return $build;
  }

}
