<?php

namespace Drupal\ad_entity;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Provides the view builder for Advertising entities.
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
    /** @var \Drupal\ad_entity\Entity\AdEntityInterface $entity */
    if ($view_mode == 'default' || $view_mode == 'full') {
      $view_mode = '["any"]';
    }
    if (strpos($view_mode, '["') !== 0) {
      $view_mode = '["' . $view_mode . '"]';
    }

    $build = [
      '#cache' => [
        'keys' => ['entity_view', 'ad_entity', $entity->id(), $view_mode],
        'bin' => $this->cacheBin,
        'tags' => Cache::mergeTags($this->getCacheTags(), $entity->getCacheTags()),
        'contexts' => $entity->getCacheContexts(),
        'max-age' => $entity->getCacheMaxAge(),
      ],
    ];
    if ($entity instanceof TranslatableInterface && count($entity->getTranslationLanguages()) > 1) {
      $build['#cache']['keys'][] = $entity->language()->getId();
    }

    // Check whether a given context wants to turn off the advertisement.
    $turnoff = $entity->getContextDataForPlugin('turnoff');
    if (!empty($turnoff)) {
      return $build;
    }

    $build += [
      '#theme' => 'ad_entity',
      '#ad_entity' => $entity,
      '#variant' => $view_mode,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = '["any"]', $langcode = NULL) {
    $build = [];
    foreach ($entities as $entity) {
      $build[$entity->id()] = $this->view($entity, $view_mode, $langcode);
    }
    return $build;
  }

}
