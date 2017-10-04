<?php

namespace Drupal\ad_entity\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines Display configurations for Advertisement.
 *
 * @ConfigEntityType(
 *   id = "ad_display",
 *   label = @Translation("Display for Advertisement"),
 *   label_collection = @Translation("Display configs for Advertisement"),
 *   label_singular = @Translation("Display for Advertisement"),
 *   label_plural = @Translation("Display configs for Advertisement"),
 *   handlers = {
 *     "list_builder" = "Drupal\ad_entity\AdDisplayListBuilder",
 *     "view_builder" = "Drupal\ad_entity\AdDisplayViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\ad_entity\Form\AdDisplayForm",
 *       "edit" = "Drupal\ad_entity\Form\AdDisplayForm",
 *       "delete" = "Drupal\ad_entity\Form\AdDisplayDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ad_entity\AdDisplayHtmlRouteProvider",
 *     },
 *    "access" = "Drupal\entity\EntityAccessControlHandler",
 *    "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *    "required_services" = "Drupal\ad_entity\AdEntityServices"
 *   },
 *   config_prefix = "display",
 *   admin_permission = "administer ad_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/ad_entity/display/{ad_display}",
 *     "add-form" = "/admin/structure/ad_entity/display/add",
 *     "edit-form" = "/admin/structure/ad_entity/display/{ad_display}/edit",
 *     "delete-form" = "/admin/structure/ad_entity/display/{ad_display}/delete",
 *     "collection" = "/admin/structure/ad_entity/display"
 *   }
 * )
 */
class AdDisplay extends ConfigEntityBase implements AdDisplayInterface {

  /**
   * The display ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The display label.
   *
   * @var string
   */
  protected $label;

  /**
   * The handler which delivers any required service.
   *
   * @var \Drupal\ad_entity\AdEntityServices
   */
  protected $services;

  /**
   * Get the handler which delivers any required service.
   *
   * @return \Drupal\ad_entity\AdEntityServices
   *   The services handler.
   */
  protected function services() {
    if (!isset($this->services)) {
      $this->services = $this->entityTypeManager()
        ->getHandler($this->getEntityTypeId(), 'required_services');
    }
    return $this->services;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    if (!empty($this->get('variants'))) {
      foreach ($this->get('variants') as $theme_variants) {
        if (!empty($theme_variants)) {
          foreach (array_keys($theme_variants) as $id) {
            $dependency = 'ad_entity.ad_entity.' . $id;
            $this->addDependency('config', $dependency);
          }
        }
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $this->cacheMaxAge = parent::getCacheMaxAge();

    $context_manager = $this->services()->getContextManager();
    foreach ($context_manager->getInvolvedEntities() as $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        if ($entity !== $this) {
          $this->cacheMaxAge = Cache::mergeMaxAges($entity->getCacheMaxAge(), $this->cacheMaxAge);
        }
      }
    }

    return $this->cacheMaxAge;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $this->cacheContexts = parent::getCacheContexts();

    $this->addCacheContexts(['url.path']);
    $context_manager = $this->services()->getContextManager();
    foreach ($context_manager->getInvolvedEntities() as $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        if ($entity !== $this) {
          $this->addCacheContexts($entity->getCacheContexts());
        }
      }
    }

    return $this->cacheContexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $this->cacheTags = parent::getCacheTags();

    $tags = ['config:ad_entity.settings'];
    if (!empty($this->get('variants'))) {
      foreach ($this->get('variants') as $theme_variants) {
        if (!empty($theme_variants)) {
          foreach (array_keys($theme_variants) as $id) {
            $tag = 'config:ad_entity.ad_entity.' . $id;
            if (!in_array($tag, $tags)) {
              $tags[] = $tag;
            }
          }
        }
      }
    }
    $this->addCacheTags($tags);

    $context_manager = $this->services()->getContextManager();
    foreach ($context_manager->getInvolvedEntities() as $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        if ($entity !== $this) {
          $this->addCacheTags($entity->getCacheTags());
        }
      }
    }

    return $this->cacheTags;
  }

}
