<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ad_entity\Plugin\AdContextManager;

/**
 * Base formatter class for Advertising context fields.
 */
abstract class ContextFormatterBase extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The Advertising context manager.
   *
   * @var \Drupal\ad_entity\Plugin\AdContextManager
   */
  protected $contextManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('ad_entity.context_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Constructs a new AdContextFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\ad_entity\Plugin\AdContextManager $context_manager
   *   The Advertising context manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AdContextManager $context_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->contextManager = $context_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'appliance_mode' => 'frontend',
      'targeting' => [
        'bundle_label' => FALSE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $options = [
      'frontend' => $this->t("Frontend appliance mode"),
      'backend' => $this->t("Backend appliance mode"),
      'both' => $this->t("Both frontend & backend"),
    ];
    $elements['appliance_mode'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t("Appliance mode"),
      '#description' => $this->t("<em>Frontend appliance mode</em> lets the client's browser apply the context via Javascript. <em>Backend appliance mode</em> lets the context being applied from server-side, which might be more suitable for iframes or feeds. The option <em>Both frontend & backend</em> appliance modes should only be considered for rare edge cases."),
      '#default_value' => $this->getSetting('appliance_mode'),
      '#required' => TRUE,
      '#weight' => 10,
    ];
    $elements['targeting'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Targeting'),
      '#description' => $this->t('<strong>Please note:</strong> These options apply for any entity which has a context field.'),
      '#weight' => 20,
    ];
    $elements['targeting']['bundle_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include "bundle: label" information'),
      '#description' => $this->t('Example: A term "red" of the "color" vocabulary would add "color: red" to the targeting.'),
      '#default_value' => !empty($this->getSetting('targeting')['bundle_label']),
      '#weight' => 10,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t("Appliance mode: @mode", ['@mode' => $this->getSetting('appliance_mode')]);
    if (!empty($this->getSetting('targeting')['bundle_label'])) {
      $summary[] = $this->t('Include "bundle: label" information');
    }
    return $summary;
  }

  /**
   * Includes the Advertising context from the given items for later appliance.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The list of field items holding Advertising context.
   *
   * @return array
   *   A render array containing Advertising context for frontend appliance.
   */
  protected function includeForAppliance(FieldItemListInterface $items) {
    $element = [];
    $appliance_mode = $this->getSetting('appliance_mode');

    // Allow other modules to act on the inclusion of Advertising context.
    $this->moduleHandler
      ->invokeAll('ad_context_include', [$items, $this->getSettings()]);

    if ($appliance_mode == 'frontend' || $appliance_mode == 'both') {
      foreach ($items as $item) {
        $element[] = $this->buildElementFromItem($item);
      }
    }

    if ($appliance_mode == 'backend' || $appliance_mode == 'both') {
      foreach ($items as $item) {
        $this->addItemToContextData($item);
      }
    }

    return $element;
  }

  /**
   * Builds a context render element from the given field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return array
   *   The context element as render array.
   */
  protected function buildElementFromItem(FieldItemInterface $item) {
    if ($context_item = $item->get('context')) {
      $id = $context_item->get('context_plugin_id')->getValue();
      if ($id && $this->contextManager->hasDefinition($id)) {
        return [
          '#theme' => 'ad_entity_context',
          '#item' => $context_item,
          '#definition' => $this->contextManager->getDefinition($id),
        ];
      }
    }
    return [];
  }

  /**
   * Adds the given field item to the collection of backend context data.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   */
  protected function addItemToContextData(FieldItemInterface $item) {
    if ($context_item = $item->get('context')) {
      $plugin_id = $context_item->get('context_plugin_id')->getValue();
      $settings = $context_item->get('context_settings')->getValue();
      $settings = !empty($settings[$plugin_id]) ? $settings[$plugin_id] : [];
      $apply_on = $context_item->get('apply_on')->getValue();
      $this->contextManager->addContextData($plugin_id, $settings, $apply_on);
    }
  }

}
