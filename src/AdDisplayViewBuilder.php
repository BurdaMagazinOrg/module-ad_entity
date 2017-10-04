<?php

namespace Drupal\ad_entity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides the view builder for Display configs for Advertisement.
 */
class AdDisplayViewBuilder extends EntityViewBuilder {

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The storage for Advertising entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $adEntityStorage;

  /**
   * The view builder for Advertising entities.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $adEntityViewBuilder;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $type_manager = $container->get('entity_type.manager');
    /** @var \Drupal\ad_entity\AdDisplayViewBuilder $instance */
    $instance = parent::createInstance($container, $entity_type);
    $instance->setThemeManager($container->get('theme.manager'));
    $instance->setAdEntityStorage($type_manager->getStorage('ad_entity'));
    $instance->setAdEntityViewBuilder($type_manager->getViewBuilder('ad_entity'));
    return $instance;
  }

  /**
   * Get the theme manager.
   *
   * @return \Drupal\Core\Theme\ThemeManagerInterface
   *   The theme manager.
   */
  public function getThemeManager() {
    return $this->themeManager;
  }

  /**
   * Set the theme manager.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager to set.
   *
   * @return \Drupal\ad_entity\AdDisplayViewBuilder
   *   The view builder itself.
   */
  public function setThemeManager(ThemeManagerInterface $theme_manager) {
    $this->themeManager = $theme_manager;
    return $this;
  }

  /**
   * Get the storage for Advertising entities.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The storage object.
   */
  public function getAdEntityStorage() {
    return $this->adEntityStorage;
  }

  /**
   * Set the storage for Advertising entities.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The storage for Advertising entities.
   *
   * @return \Drupal\ad_entity\AdDisplayViewBuilder
   *   The view builder itself.
   */
  public function setAdEntityStorage(EntityStorageInterface $storage) {
    $this->adEntityStorage = $storage;
    return $this;
  }

  /**
   * Get the view builder for Advertising entities.
   *
   * @return \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  public function getAdEntityViewBuilder() {
    return $this->adEntityViewBuilder;
  }

  /**
   * Set the view builder for Advertising entities.
   *
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder
   *   The view builder for Advertising entities.
   *
   * @return \Drupal\ad_entity\AdDisplayViewBuilder
   *   The view builder itself.
   */
  public function setAdEntityViewBuilder(EntityViewBuilderInterface $view_builder) {
    $this->adEntityViewBuilder = $view_builder;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = [
      '#cache' => [
        'tags' => Cache::mergeTags($this->getCacheTags(), $entity->getCacheTags()),
        'contexts' => $entity->getCacheContexts(),
        'max-age' => $entity->getCacheMaxAge(),
      ],
    ];
    if ($this->isViewModeCacheable($view_mode) && !$entity->isNew() && $this->entityType->isRenderCacheable()) {
      $build['#cache'] += [
        'keys' => [
          'entity_view',
          $entity->getEntityTypeId(),
          $entity->id(),
          $view_mode,
        ],
        'bin' => $this->cacheBin,
      ];

      if ($entity instanceof TranslatableInterface && count($entity->getTranslationLanguages()) > 1) {
        $build['#cache']['keys'][] = $entity->language()->getId();
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {}

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'default', $langcode = NULL) {
    $build = $this->viewMultiple([$entity], $view_mode, $langcode);
    return !empty($build) ? reset($build) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'default', $langcode = NULL) {
    $theme = $this->themeManager->getActiveTheme();
    $build_list = [];

    foreach ($entities as $ad_display) {
      $theme_name = $theme->getName();
      $variants = $ad_display->get('variants') ?: [];
      if (empty($variants[$theme_name])) {
        // Check for enabled fallback settings, and switch to these when given.
        $fallback = $ad_display->get('fallback') ?: [];
        if (!empty($fallback['use_settings_from'])) {
          $theme_name = $fallback['use_settings_from'];
        }
        if (!empty($fallback['use_base_theme'])) {
          foreach ($theme->getBaseThemes() as $base_theme) {
            if (!empty($variants[$base_theme->getName()])) {
              $theme_name = $base_theme->getName();
              break;
            }
          }
        }
      }

      $build = $this->getBuildDefaults($ad_display, $view_mode);
      // When given, load and view the assigned Advertisement.
      if (!empty($variants[$theme_name])) {
        foreach ($variants[$theme_name] as $id => $variant) {
          if ($ad_entity = $this->adEntityStorage->load($id)) {
            if ($ad_entity->access('view')) {
              $build[$ad_entity->id()] = $this
                ->adEntityViewBuilder->view($ad_entity, $variant);
            }
          }
        }
      }
      $build_list[$ad_display->id()] = $build;
    }

    return $build_list;
  }

}
