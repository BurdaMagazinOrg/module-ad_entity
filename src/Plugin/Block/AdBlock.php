<?php

namespace Drupal\ad_entity\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\theme_breakpoints_js\ThemeBreakpointsJs;
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
   * The theme breakpoints js manager.
   *
   * @var \Drupal\theme_breakpoints_js\ThemeBreakpointsJs
   */
  protected $themeBreakpointsJs;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $type_manager = $container->get('entity_type.manager');
    $ad_entity_storage = $type_manager->getStorage('ad_entity');
    $ad_entity_view_builder = $type_manager->getViewBuilder('ad_entity');
    $theme_breakpoints_js = $container->get('theme_breakpoints_js');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $ad_entity_storage,
      $ad_entity_view_builder,
      $theme_breakpoints_js
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
   * @param \Drupal\theme_breakpoints_js\ThemeBreakpointsJs $theme_breakpoints_js
   *   The theme breakpoints js manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $ad_entity_storage, EntityViewBuilderInterface $ad_entity_view_builder, ThemeBreakpointsJs $theme_breakpoints_js) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adEntityStorage = $ad_entity_storage;
    $this->adEntityViewBuilder = $ad_entity_view_builder;
    $this->themeBreakpointsJs = $theme_breakpoints_js;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $theme_name = $form_state->get('block_theme');
    $theme_breakpoints = $this->themeBreakpointsJs->getBreakpointsForThemeName($theme_name);

    $entities = $this->adEntityStorage->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $options[$entity->id()] = $entity->label();
    }
    $form['ad_entity_any'] = [
      '#type' => 'select',
      '#title' => $this->t("Default entity for any screen width"),
      '#description' => $this->t("The selected Advertising entity will always be displayed, regardless of the given screen width. <strong>Choose none</strong> if you want to use variants per breakpoint."),
      '#empty_value' => '',
      '#required' => FALSE,
      '#options' => $options,
      '#default_value' => !empty($this->configuration['ad_entity_any']) ? $this->configuration['ad_entity_any'] : NULL,
    ];
    $form['breakpoint_hint'] = [
      '#markup' => $this->t("<strong>For variants, make sure that the theme has its breakpoints properly set up.</strong>"),
    ];
    foreach ($theme_breakpoints as $variant => $breakpoint) {
      $form['ad_entity_' . $variant] = [
        '#type' => 'select',
        '#title' => $this->t("Variant for @breakpoint", ['@breakpoint' => $breakpoint->getLabel()]),
        '#description' => $this->t("The selected Advertising entity will be displayed on @breakpoint screen width.", ['@breakpoint' => $breakpoint->getLabel()]),
        '#empty_value' => '',
        '#required' => FALSE,
        '#options' => $options,
        '#default_value' => !empty($this->configuration['ad_entity_' . $variant]) ? $this->configuration['ad_entity_' . $variant] : NULL,
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
    $theme_name = $form_state->get('block_theme');

    $this->configuration['ad_entity_theme'] = $theme_name;
    $this->configuration['ad_entity_any']
      = $form_state->getValue('ad_entity_any');

    $theme_breakpoints = $this->themeBreakpointsJs->getBreakpointsForThemeName($theme_name);

    foreach ($theme_breakpoints as $variant => $breakpoint) {
      $this->configuration['ad_entity_' . $variant]
        = $form_state->getValue('ad_entity_' . $variant);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $config = $this->getConfiguration();
    $dependencies = ['config' => []];

    $theme_breakpoints = $this->themeBreakpointsJs->getBreakpointsForThemeName(
      $config['ad_entity_theme']
    );
    foreach (array_merge($theme_breakpoints, ['any' => '']) as $variant => $breakpoint) {
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
    $tags = [];
    $config = $this->getConfiguration();
    $theme_breakpoints = $this->themeBreakpointsJs->getBreakpointsForThemeName(
      $config['ad_entity_theme']
    );
    foreach (array_merge($theme_breakpoints, ['any' => '']) as $variant => $breakpoint) {
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
      $config = $this->getConfiguration();
      $theme_breakpoints = $this->themeBreakpointsJs->getBreakpointsForThemeName(
        $config['ad_entity_theme']
      );
      foreach ($theme_breakpoints as $variant => $breakpoint) {
        $id = !empty($this->configuration['ad_entity_' . $variant]) ?
          $this->configuration['ad_entity_' . $variant] : NULL;
        if ($id && ($ad_entity = $this->adEntityStorage->load($id))) {
          if ($ad_entity->access('view')) {
            $build[] = $this->adEntityViewBuilder->view($ad_entity, $variant);
          }
        }
      }
    }
    return $build;
  }

}
