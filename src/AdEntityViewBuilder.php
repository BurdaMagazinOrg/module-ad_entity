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
  public function view(EntityInterface $entity, $view_mode = 'any', $langcode = NULL) {
    $build = $this->viewMultiple([$entity], $view_mode, $langcode);
    return reset($build);
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'any', $langcode = NULL) {
    /** @var \Drupal\ad_entity\Entity\AdEntityInterface[] $entities */
    $build = [];
    foreach ($entities as $entity) {
      $entity_id = $entity->id();

      // Build the view. No caching is defined here,
      // because there might be multiple blocks on one page
      // using the same advertising entity.
      // Advertising blocks will be cached anyway.
      $build[$entity_id] = [
        '#theme' => 'ad_entity',
        '#ad_entity' => $entity,
        '#breakpoint' => $view_mode,
      ];
    }

    return $build;
  }

}
