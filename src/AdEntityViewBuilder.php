<?php

namespace Drupal\ad_entity;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Cache\CacheableMetadata;

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
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = $this->viewMultiple([$entity], $view_mode, $langcode);
    return reset($build);
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'full', $langcode = NULL) {
    /** @var \Drupal\ad_entity\Entity\AdEntityInterface[] $entities */
    $build = [];
    foreach ($entities as $entity) {
      $entity_id = $entity->id();

      // Build the cache info.
      $build[$entity_id] = [
        '#cache' => ['keys' => ['entity_view', 'ad_entity', $entity_id]],
      ];
      $cacheable_metadata = CacheableMetadata::createFromObject($entity);
      $cacheable_metadata->addCacheTags($this->getCacheTags());
      $cacheable_metadata->applyTo($build[$entity_id]);

      // Build the content.
      // TODO Use view handler plugins to build the content.
      $build[$entity_id] += [];
    }

    return $build;
  }

}
