<?php

namespace Drupal\ad_entity\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ad_entity\Plugin\AdContextManager;

/**
 * Plugin implementation of the 'ad_entity_context' field widget.
 *
 * @FieldWidget(
 *   id = "ad_entity_context",
 *   label = @Translation("Advertising context"),
 *   field_types = {
 *     "ad_entity_context"
 *   }
 * )
 */
class ContextWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The Advertising context manager.
   *
   * @var \Drupal\ad_entity\Plugin\AdContextManager
   */
  protected $contextManager;

  /**
   * The storage for Advertising entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $adEntityStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('ad_entity.context_manager'),
      $container->get('entity_type.manager')->getStorage('ad_entity')
    );
  }

  /**
   * Constructs a AdContextWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\ad_entity\Plugin\AdContextManager $context_manager
   *   The Advertising context manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $ad_storage
   *   The storage for Advertising entities.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, AdContextManager $context_manager, EntityStorageInterface $ad_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->contextManager = $context_manager;
    $this->adEntityStorage = $ad_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'context_plugin_id' => '',
      'apply_on' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $context = $items->get($delta)->get('context')->getValue();

    $context_definitions = $this->contextManager->getDefinitions();
    $options = [];
    foreach ($context_definitions as $id => $definition) {
      $options[$id] = $definition['label'];
    }
    $plugin_id = !empty($context['context_plugin_id']) ? $context['context_plugin_id'] : NULL;
    $element['context_plugin_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#required' => TRUE,
      '#options' => $options,
      '#empty_value' => '',
      '#default_value' => $plugin_id,
    ];

    /** @var \Drupal\ad_entity\Entity\AdEntityInterface[] $entities */
    $entities = $this->adEntityStorage->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $options[$entity->id()] = $entity->label();
    }
    $apply_on = !empty($context['apply_on']) ? $context['apply_on'] : NULL;
    $element['apply_on'] = [
      '#type' => 'select',
      '#title' => $this->t('Apply on ads'),
      '#required' => TRUE,
      '#multiple' => TRUE,
      '#options' => $options,
      '#empty_value' => '',
      '#default_value' => $apply_on,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    return ['context' => $values];
  }

}
