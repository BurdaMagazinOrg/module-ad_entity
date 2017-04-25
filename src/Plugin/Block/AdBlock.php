<?php

namespace Drupal\ad_entity\Plugin\Block;

use Drupal\breakpoint\BreakpointInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines Advertising blocks.
 *
 * @Block(
 *   id = "ad_entity_block",
 *   admin_label = @Translation("Advertising block")
 * )
 */
class AdBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * List of supported devices.
   *
   * @var array
   */
  static protected $devices = ['smartphone', 'tablet', 'desktop'];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $type_manager = $container->get('entity_type.manager');
    $ad_entity_storage = $type_manager->getStorage('ad_entity');
    $ad_entity_view_builder = $type_manager->getViewBuilder('ad_entity');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $ad_entity_storage,
      $ad_entity_view_builder
    );
  }

  /**
   * AdBlock constructor.
   *
   * @param array $configuration
   *   The configuration array.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $ad_entity_storage
   *   The storage for Advertising entities.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $ad_entity_view_builder
   *   The view builder for Advertising entities.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $ad_entity_storage, EntityViewBuilderInterface $ad_entity_view_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adEntityStorage = $ad_entity_storage;
    $this->adEntityViewBuilder = $ad_entity_view_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $themeBreakpoints = $this->getBreakpointsForFormState($form_state);

    $entities = $this->adEntityStorage->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $options[$entity->id()] = $entity->label();
    }
    $form['ad_entity_any'] = [
      '#type' => 'select',
      '#title' => $this->t("Default entity for any device"),
      '#description' => $this->t("The selected Advertising entity will always be displayed, regardless of the given device. <strong>Choose none</strong> if you want to use variants per device."),
      '#empty_value' => '',
      '#required' => FALSE,
      '#options' => $options,
      '#default_value' => !empty($this->configuration['ad_entity_any']) ? $this->configuration['ad_entity_any'] : NULL,
    ];
    $form['breakpoint_hint'] = [
      '#markup' => $this->t("<strong>For variants, make sure that the theme has its breakpoints properly set up.</strong>"),
    ];
    foreach ($themeBreakpoints as $breakpoint) {
      $form['ad_entity_' . $breakpoint->getLabel()] = [
        '#type' => 'select',
        '#title' => $this->t("Variant for @device", ['@device' => $breakpoint->getLabel()]),
        '#description' => $this->t("The selected Advertising entity will be displayed on @device devices.", ['@device' => $breakpoint->getLabel()]),
        '#empty_value' => '',
        '#required' => FALSE,
        '#options' => $options,
        '#default_value' => !empty($this->configuration['ad_entity_' . $breakpoint->getLabel()]) ? $this->configuration['ad_entity_' . $breakpoint->getLabel()] : NULL,
        '#states' => [
          'visible' => [
            'select[name="settings[ad_entity_any]"]' => ['value' => ''],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $themeBreakpoints = $this->getBreakpointsForFormState($form_state);

    $this->configuration['ad_entity_any']
      = $form_state->getValue('ad_entity_any');

    foreach ($themeBreakpoints as $variant) {
      $this->configuration['ad_entity_' . $variant->getLabel()]
        = $form_state->getValue('ad_entity_' . $variant->getLabel());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $config = $this->getConfiguration();
    $dependencies = ['config' => []];
    // TODO change this to use themeBreakpoints
    foreach (array_merge(self::$devices, ['any']) as $variant) {
      if (!empty($config['ad_entity_' . $variant])) {
        $dependency = 'ad_entity.ad_entity.' . $config['ad_entity_' . $variant];
        if (!in_array($dependency, $dependencies['config'])) {
          $dependencies['config'][] = $dependency;
        }
      }
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['config:breakpoint_js_settings.settings'];
    $config = $this->getConfiguration();
    // TODO change this to use themeBreakpoints
    foreach (array_merge(self::$devices, ['any']) as $variant) {
      if (!empty($config['ad_entity_' . $variant])) {
        $tags[] = 'config:ad_entity.ad_entity.' . $config['ad_entity_' . $variant];
      }
    }
    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if (!empty($this->configuration['ad_entity_any'])) {
      $id = $this->configuration['ad_entity_any'];
      if ($ad_entity = $this->adEntityStorage->load($id)) {
        if ($ad_entity->access('view')) {
          $build[] = $this->adEntityViewBuilder->view($ad_entity, 'any');
        }
      }
    }
    else {
      $themeBreakpoints = $this->getBreakpointsForActiveTheme();
      foreach ($themeBreakpoints as $name => $variant) {
        $id = !empty($this->configuration['ad_entity_' . $variant->getLabel()]) ?
          $this->configuration['ad_entity_' . $variant->getLabel()] : NULL;
        if ($id && ($ad_entity = $this->adEntityStorage->load($id))) {
          if ($ad_entity->access('view')) {
            $build[] = $this->adEntityViewBuilder->view($ad_entity, $name);
          }
        }
      }
    }
    return $build;
  }

  /**
   * @return \Drupal\breakpoint\BreakpointInterface[]
   */
  private function getBreakpointsForActiveTheme() {
    /** @var \Drupal\Core\Theme\ThemeManager $themeManager */
    $themeManager = \Drupal::service('theme.manager');
    $activeTheme = $themeManager->getActiveTheme()->getName();

    return $this->getThemeBreakpoints($activeTheme);
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\breakpoint\BreakpointInterface[]
   */
  private function getBreakpointsForFormState(FormStateInterface $form_state) {
    $blockTheme = $form_state->get('block_theme');
    return $this->getThemeBreakpoints($blockTheme);
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return BreakpointInterface[]
   */
  private function getThemeBreakpoints($theme) {
    $breakpointManager = \Drupal::service('breakpoint.manager');
    /** @var \Drupal\breakpoint\BreakpointInterface[] $themeBreakpoints */
    $themeBreakpoints = $breakpointManager->getBreakpointsByGroup($theme);
    return $themeBreakpoints;
  }

}
