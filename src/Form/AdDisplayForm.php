<?php

namespace Drupal\ad_entity\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Component\Serialization\Json;
use Drupal\theme_breakpoints_js\ThemeBreakpointsJs;

/**
 * Class AdDisplayForm.
 *
 * @package Drupal\ad_entity\Form
 */
class AdDisplayForm extends EntityForm {

  /**
   * The storage for Advertising entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $adEntityStorage;

  /**
   * The theme breakpoints js manager.
   *
   * @var \Drupal\theme_breakpoints_js\ThemeBreakpointsJs
   */
  protected $themeBreakpointsJs;

  /**
   * A list of all existent Advertising entities.
   *
   * @var \Drupal\ad_entity\Entity\AdEntityInterface[]
   */
  protected $adEntities;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $ad_entity_storage
   *   The storage for Advertising entities.
   * @param \Drupal\theme_breakpoints_js\ThemeBreakpointsJs $theme_breakpoints_js
   *   The theme breakpoints js manager.
   */
  public function __construct(EntityStorageInterface $ad_entity_storage, ThemeBreakpointsJs $theme_breakpoints_js) {
    $this->adEntityStorage = $ad_entity_storage;
    $this->themeBreakpointsJs = $theme_breakpoints_js;
    $this->adEntities = $this->adEntityStorage->loadMultiple();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $type_manager = $container->get('entity_type.manager');
    return new static(
      $type_manager->getStorage('ad_entity'),
      $container->get('theme_breakpoints_js')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (empty($this->adEntities)) {
      return [
        '#markup' => $this->t('For being able to create Display configurations for Advertisement, you need to create at least one Advertising entity first.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\ad_entity\Entity\AdDisplayInterface $ad_display */
    $ad_display = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label for the Display config'),
      '#maxlength' => 255,
      '#default_value' => $ad_display->label(),
      '#description' => $this->t("Useful parts of the label could be information about <em>placement and / or usage.</em>"),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $ad_display->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ad_entity\Entity\AdDisplay::load',
      ],
      '#disabled' => !$ad_display->isNew(),
    ];

    $default_theme = $this->configFactory()->get('system.theme') ?
      $this->configFactory()->get('system.theme')->get('default') : NULL;
    $selected_theme = $form_state->get('block_theme');

    $installed_themes = $this->configFactory()->get('core.extension')->get('theme') ?: [];
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
    $options = [];
    foreach ($this->adEntities as $entity) {
      $options[$entity->id()] = $entity->label();
    }

    // Provide settings per theme.
    $variants = $ad_display->get('variants');
    foreach ($installed_themes as $index => $theme_name) {
      $theme_breakpoints = $this->themeBreakpointsJs->getBreakpoints($theme_name);

      $variants_by_entity = !empty($variants[$theme_name]) ? $variants[$theme_name] : [];
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
        '#parents' => ['theme', $theme_name],
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
            '#title' => $this->t("Variant for breakpoint @breakpoint", ['@breakpoint' => $breakpoint->getLabel()]),
            '#description' => $this->t("The selected Advertising entity will be displayed on @breakpoint screen width.", ['@breakpoint' => $breakpoint->getLabel()]),
            '#empty_value' => '',
            '#required' => FALSE,
            '#options' => $options,
            '#default_value' => !empty($variants_by_breakpoint[$variant]) ? $variants_by_breakpoint[$variant] : NULL,
            '#states' => [
              'visible' => [
                'select[name="theme[' . $theme_name . '][variant_any]"]' => ['value' => ''],
              ],
            ],
          ];
        }
      }
    }

    $fallback_settings = $ad_display->get('fallback');
    $form['fallback'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Fallback settings'),
      '#tree' => TRUE,
      '#parents' => ['fallback'],
    ];
    $form['fallback']['description'] = [
      '#markup' => $this->t("Define what to do, when a theme is used which has no Advertisement assigned at the display settings above."),
    ];
    $form['fallback']['use_base_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use the display settings of a base theme, if available.'),
      '#default_value' => !empty($fallback_settings['use_base_theme']),
    ];
    $form['fallback']['use_settings_from'] = [
      '#type' => 'select',
      '#title' => $this->t("Use display settings of theme"),
      '#options' => array_combine($installed_themes, $installed_themes),
      '#empty_value' => '',
      '#default_value' => !empty($fallback_settings['use_settings_from']) ? $fallback_settings['use_settings_from'] : '',
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $theme_settings = $form_state->getValue('theme') ?: [];
    /** @var \Drupal\ad_entity\Entity\AdDisplayInterface $ad_display */
    $ad_display = $this->entity;

    $variants = $ad_display->get('variants');
    foreach ($theme_settings as $theme_name => $settings) {
      $theme_breakpoints = $this->themeBreakpointsJs->getBreakpoints($theme_name);

      $variants[$theme_name] = [];
      foreach (array_merge(array_keys($theme_breakpoints), ['any']) as $variant) {
        if (!empty($settings['variant_' . $variant])) {
          $id = $settings['variant_' . $variant];
          $variants[$theme_name][$id][] = $variant;
        }
      }
      foreach ($variants[$theme_name] as $id => $theme_variants) {
        $variants[$theme_name][$id] = Json::encode($theme_variants);
      }
    }
    $ad_display->set('variants', $variants);

    $fallback_settings = $ad_display->get('fallback');
    $fallback_settings['use_base_theme'] = (bool) $form_state->getValue(['fallback', 'use_base_theme']);
    $fallback_settings['use_settings_from'] = $form_state->getValue(['fallback', 'use_settings_from']);
    $ad_display->set('fallback', $fallback_settings);

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $ad_display = $this->entity;
    $status = $ad_display->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label display configuration.', [
          '%label' => $ad_display->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label display configuration.', [
          '%label' => $ad_display->label(),
        ]));
    }
    $form_state->setRedirectUrl($ad_display->toUrl('collection'));
  }

}
