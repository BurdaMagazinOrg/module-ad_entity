<?php

namespace Drupal\ad_entity\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines blocks for displaying Advertisement.
 *
 * @Block(
 *   id = "ad_display",
 *   admin_label = @Translation("Display for Advertisement"),
 *   deriver = "Drupal\ad_entity\Plugin\Derivative\AdDisplayBlock"
 * )
 */
class AdDisplayBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The storage of Display configs for Advertisement.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $adDisplayStorage;

  /**
   * The view builder for Display configs for Advertisement.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $adDisplayViewBuilder;

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [
      'module' => ['ad_entity'],
      'config' => ['ad_entity.display.' . $this->getDerivativeId()],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $type_manager = $container->get('entity_type.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $type_manager->getStorage('ad_display'),
      $type_manager->getViewBuilder('ad_display')
    );
  }

  /**
   * AdDisplayBlock constructor.
   *
   * @param array $configuration
   *   The configuration array.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $ad_display_storage
   *   The storage of Display configs for Advertisement.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $ad_display_view_builder
   *   The view builder for Display configs for Advertisement.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $ad_display_storage, EntityViewBuilderInterface $ad_display_view_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adDisplayStorage = $ad_display_storage;
    $this->adDisplayViewBuilder = $ad_display_view_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = $this->getDerivativeId();
    $build = [];
    if ($ad_display = $this->adDisplayStorage->load($id)) {
      $view = $this->adDisplayViewBuilder->view($ad_display, 'default');
      if (!empty($view['#cache'])) {
        // Let the Block alone care for caching.
        unset($view['#cache']['keys']);
        unset($view['#cache']['bin']);
        $build[$id]['#cache'] = $view['#cache'];
      }
      if ($ad_display->access('view')) {
        $build[$id] = $view;
      }
    }
    return $build;
  }

}
