<?php

namespace Drupal\ad_entity\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\ad_entity\Plugin\AdContextManager;
use Drupal\theme_breakpoints_js\ThemeBreakpointsJs;

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
   * The manager for Advertising context plugins and data.
   *
   * @var \Drupal\ad_entity\Plugin\AdContextManager
   */
  protected $adContextManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

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
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $type_manager->getStorage('ad_entity'),
      $type_manager->getViewBuilder('ad_entity'),
      $container->get('ad_entity.context_manager'),
      $container->get('config.factory'),
      $container->get('theme.manager'),
      $container->get('theme_breakpoints_js')
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
   * @param \Drupal\ad_entity\Plugin\AdContextManager $context_manager
   *   The manager for Advertising context plugins and data.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager service.
   * @param \Drupal\theme_breakpoints_js\ThemeBreakpointsJs $theme_breakpoints_js
   *   The theme breakpoints js manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $ad_entity_storage, EntityViewBuilderInterface $ad_entity_view_builder, AdContextManager $context_manager, ConfigFactoryInterface $config_factory, ThemeManagerInterface $theme_manager, ThemeBreakpointsJs $theme_breakpoints_js) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adEntityStorage = $ad_entity_storage;
    $this->adEntityViewBuilder = $ad_entity_view_builder;
    $this->adContextManager = $context_manager;
    $this->configFactory = $config_factory;
    $this->themeManager = $theme_manager;
    $this->themeBreakpointsJs = $theme_breakpoints_js;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $default_theme = $this->configFactory->get('system.theme') ?
      $this->configFactory->get('system.theme')->get('default') : NULL;
    $selected_theme = $form_state->get('block_theme');

    $installed_themes = $this->configFactory->get('core.extension')->get('theme') ?: [];
    // Change orders: default and selected theme should appear first.
    $installed_themes = array_keys($installed_themes);
    foreach ($installed_themes as $index => $theme_name) {
      if ($default_theme == $theme_name || $selected_theme == $theme_name) {
        unset($installed_themes[$index]);
      }
    }
    if (!empty($default_theme)) {
      array_unshift($installed_themes, $default_theme);
    }
    if (!empty($selected_theme) && ($default_theme != $selected_theme)) {
      array_unshift($installed_themes, $selected_theme);
    }

    // Get all Advertising entities to choose from.
    $entities = $this->adEntityStorage->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $options[$entity->id()] = $entity->label();
    }

    // Provide settings per theme.
    foreach ($installed_themes as $index => $theme_name) {
      $theme_breakpoints = $this->themeBreakpointsJs->getBreakpoints($theme_name);

      $variants_by_entity = !empty($this->configuration['variants'][$theme_name]) ? $this->configuration['variants'][$theme_name] : [];
      $variants_by_breakpoint = [];
      foreach ($variants_by_entity as $entity_id => $variant) {
        $variant = Json::decode($variant);
        foreach ($variant as $breakpoint) {
          $variants_by_breakpoint[$breakpoint] = $entity_id;
        }
      }

      $form['theme'][$theme_name] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Display settings for theme "@theme"', ['@theme' => $theme_name]),
        '#collapsible' => TRUE,
        '#collapsed' => $index > 0 ? TRUE : FALSE,
        '#tree' => TRUE,
      ];
      $form['theme'][$theme_name]['variant_any'] = [
        '#type' => 'select',
        '#title' => $this->t("Default entity for any screen width"),
        '#description' => !empty($theme_breakpoints) ? $this->t("The selected Advertising entity will always be displayed, regardless of the given screen width. <strong>Choose none</strong> if you want to use variants per breakpoint.") : '',
        '#empty_value' => '',
        '#required' => FALSE,
        '#options' => $options,
        '#default_value' => !empty($variants_by_breakpoint['any']) ? $variants_by_breakpoint['any'] : NULL,
      ];
      if (!empty($theme_breakpoints)) {
        $form['theme'][$theme_name]['breakpoint_hint'] = [
          '#markup' => $this->t("<strong>When using variants, make sure that the theme has its breakpoints properly set up.</strong>"),
        ];
        foreach ($theme_breakpoints as $variant => $breakpoint) {
          $form['theme'][$theme_name]['variant_' . $variant] = [
            '#type' => 'select',
            '#title' => $this->t("Variant for @breakpoint", ['@breakpoint' => $breakpoint->getLabel()]),
            '#description' => $this->t("The selected Advertising entity will be displayed on @breakpoint screen width.", ['@breakpoint' => $breakpoint->getLabel()]),
            '#empty_value' => '',
            '#required' => FALSE,
            '#options' => $options,
            '#default_value' => !empty($variants_by_breakpoint[$variant]) ? $variants_by_breakpoint[$variant] : NULL,
            '#states' => [
              'visible' => [
                'select[name="settings[theme][' . $theme_name . '][variant_any]"]' => ['value' => ''],
              ],
            ],
          ];
        }
      }
    }

    $form['fallback'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Fallback settings'),
      '#tree' => TRUE,
    ];
    $form['fallback']['description'] = [
      '#markup' => $this->t("Define what to do, when a theme is used which has no Advertisement assigned at the display settings above."),
    ];
    $form['fallback']['use_base_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use the display settings of a base theme, if available.'),
      '#default_value' => !empty($this->configuration['fallback']['use_base_theme']),
    ];
    $form['fallback']['use_settings_from'] = [
      '#type' => 'select',
      '#title' => $this->t("Use display settings of theme"),
      '#options' => array_combine($installed_themes, $installed_themes),
      '#empty_value' => '',
      '#default_value' => !empty($this->configuration['fallback']['use_settings_from']) ? $this->configuration['fallback']['use_settings_from'] : '',
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $theme_settings = $form_state->getValue('theme') ?: [];

    foreach ($theme_settings as $theme_name => $settings) {
      $theme_breakpoints = $this->themeBreakpointsJs->getBreakpoints($theme_name);

      $this->configuration['variants'][$theme_name] = [];
      foreach (array_merge(array_keys($theme_breakpoints), ['any']) as $variant) {
        if (!empty($settings['variant_' . $variant])) {
          $id = $settings['variant_' . $variant];
          $this->configuration['variants'][$theme_name][$id][] = $variant;
        }
      }
      foreach ($this->configuration['variants'][$theme_name] as $id => $variants) {
        $this->configuration['variants'][$theme_name][$id] = Json::encode($variants);
      }
    }

    $this->configuration['fallback']['use_base_theme'] = (bool) $form_state->getValue(['fallback', 'use_base_theme']);
    $this->configuration['fallback']['use_settings_from'] = $form_state->getValue(['fallback', 'use_settings_from']);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = ['config' => []];
    $config = $this->getConfiguration();

    if (!empty($config['variants'])) {
      foreach ($config['variants'] as $theme_variants) {
        if (!empty($theme_variants)) {
          foreach (array_keys($theme_variants) as $id) {
            $dependency = 'ad_entity.ad_entity.' . $id;
            if (!in_array($dependency, $dependencies['config'])) {
              $dependencies['config'][] = $dependency;
            }
          }
        }
      }
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $max_age = parent::getCacheMaxAge();
    foreach ($this->adContextManager->getInvolvedEntities() as $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        $max_age = Cache::mergeMaxAges($entity->getCacheMaxAge(), $max_age);
      }
    }
    return $max_age;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = ['url.path'];
    foreach ($this->adContextManager->getInvolvedEntities() as $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        $contexts = Cache::mergeContexts($entity->getCacheContexts(), $contexts);
      }
    }
    return Cache::mergeContexts(parent::getCacheContexts(), $contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['config:ad_entity.settings'];
    $config = $this->getConfiguration();

    if (!empty($config['variants'])) {
      foreach ($config['variants'] as $theme_variants) {
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

    foreach ($this->adContextManager->getInvolvedEntities() as $entities) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      foreach ($entities as $entity) {
        $tags = Cache::mergeTags($entity->getCacheTags(), $tags);
      }
    }

    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $theme = $this->themeManager->getActiveTheme();
    $theme_name = $theme->getName();
    if (empty($this->configuration['variants'][$theme_name])) {
      // Check for enabled fallback settings, and switch to these when given.
      if (!empty($this->configuration['fallback']['use_settings_from'])) {
        $theme_name = $this->configuration['fallback']['use_settings_from'];
      }
      if (!empty($this->configuration['fallback']['use_base_theme'])) {
        foreach ($theme->getBaseThemes() as $base_theme) {
          if (!empty($this->configuration['variants'][$base_theme->getName()])) {
            $theme_name = $base_theme->getName();
            break;
          }
        }
      }
    }

    // When given, load and view the assigned Advertisement.
    $build = [];
    if (!empty($this->configuration['variants'][$theme_name])) {
      foreach ($this->configuration['variants'][$theme_name] as $id => $variant) {
        if ($ad_entity = $this->adEntityStorage->load($id)) {
          if ($ad_entity->access('view')) {
            $build[] = $this->adEntityViewBuilder->view($ad_entity, $variant);
          }
        }
      }
    }
    return $build;
  }

}
