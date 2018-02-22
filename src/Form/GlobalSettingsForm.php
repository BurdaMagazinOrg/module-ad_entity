<?php

namespace Drupal\ad_entity\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ad_entity\Plugin\AdTypeManager;

/**
 * Class GlobalSettingsForm.
 *
 * @package Drupal\ad_entity\Form
 */
class GlobalSettingsForm extends ConfigFormBase {

  /**
   * The Advertising type manager.
   *
   * @var \Drupal\ad_entity\Plugin\AdTypeManager
   */
  protected $typeManager;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\ad_entity\Plugin\AdTypeManager $ad_type_manager
   *   The Advertising type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AdTypeManager $ad_type_manager) {
    parent::__construct($config_factory);
    $this->typeManager = $ad_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('ad_entity.type_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ad_entity.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ad_entity_settings';
  }

  /**
   * Get the mutable config object which belongs to this form.
   *
   * @return \Drupal\Core\Config\Config
   *   The mutable config object.
   */
  public function getConfig() {
    return $this->config('ad_entity.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->getConfig();

    $form['common'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Settings for any type of advertisement'),
      '#weight' => 10,
    ];
    $form['common']['frontend'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Frontend'),
      '#weight' => 10,
    ];
    $default_behavior = $config->get('enable_responsive_behavior') !== NULL ?
      (int) $config->get('enable_responsive_behavior') : 1;
    $form['common']['frontend']['enable_responsive_behavior'] = [
      '#type' => 'radios',
      '#title' => $this->t('Responsive behavior'),
      '#options' => [0 => $this->t("Disabled"), 1 => $this->t("Enabled")],
      '#description' => $this->t("When enabled, advertisement will be dynamically initialized on breakpoint changes (e.g. when switching from narrow to wide). When disabled, advertisement will only be initialized based on the initial breakpoint during page load."),
      '#default_value' => $default_behavior,
      '#weight' => 10,
    ];
    $behavior_reset = $config->get('behavior_on_context_reset');
    $form['common']['behavior_on_context_reset'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Behavior when backend context has been reset'),
      '#weight' => 20,
    ];
    $form['common']['behavior_on_context_reset']['info'] = [
      '#prefix' => '<div class="description">',
      '#suffix' => '</div>',
      '#markup' => $this->t('Advertising context will be reset to the scope of an entity from the route and anytime an entity is being viewed which delivers its own context.'),
      '#weight' => 10,
    ];
    $form['common']['behavior_on_context_reset']['include_entity_info'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include elementary targeting information about the entity scope (type, label, uuid)'),
      '#parents' => ['behavior_on_context_reset', 'include_entity_info'],
      '#default_value' => isset($behavior_reset['include_entity_info']) ? (int) $behavior_reset['include_entity_info'] : 1,
      '#weight' => 20,
    ];
    $form['common']['behavior_on_context_reset']['collect_default_data'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enforce the collection of default Advertising context from the entity scope'),
      '#description' => $this->t('When enabled, backend context data will be collected from the context fields, which are enabled in the <b>default view mode</b> for the entity.'),
      '#parents' => ['behavior_on_context_reset', 'collect_default_data'],
      '#default_value' => isset($behavior_reset['collect_default_data']) ? (int) $behavior_reset['collect_default_data'] : 1,
      '#weight' => 30,
    ];

    $type_ids = array_keys($this->typeManager->getDefinitions());

    if (!empty($type_ids)) {
      $form['settings_tabs'] = [
        '#type' => 'vertical_tabs',
        '#default_tab' => 'edit-' . key($type_ids),
        '#weight' => 20,
      ];

      foreach ($type_ids as $type_id) {
        /** @var \Drupal\ad_entity\Plugin\AdTypeInterface $type */
        $type = $this->typeManager->createInstance($type_id);
        $label = $type->getPluginDefinition()['label'];
        $form[$type_id] = [
          '#type' => 'details',
          '#group' => 'settings_tabs',
          '#attributes' => ['id' => 'edit-' . $type_id],
          '#title' => $this->t("@type types", ['@type' => $label]),
          '#tree' => TRUE,
        ] + $type->globalSettingsForm($form, $form_state, $config);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $config = $this->getConfig();

    $type_ids = array_keys($this->typeManager->getDefinitions());
    foreach ($type_ids as $type_id) {
      /** @var \Drupal\ad_entity\Plugin\AdTypeInterface $type */
      $type = $this->typeManager->createInstance($type_id);
      $type->globalSettingsValidate($form, $form_state, $config);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->getConfig();
    $config->set('enable_responsive_behavior', (bool) $form_state->getValue('enable_responsive_behavior'));
    $config->set('behavior_on_context_reset', $form_state->getValue('behavior_on_context_reset'));

    $type_ids = array_keys($this->typeManager->getDefinitions());
    foreach ($type_ids as $type_id) {
      $values = $form_state->getValue($type_id, []);
      if (!empty($values)) {
        $config->set($type_id, $values);
      }

      /** @var \Drupal\ad_entity\Plugin\AdTypeInterface $type */
      $type = $this->typeManager->createInstance($type_id);
      $type->globalSettingsSubmit($form, $form_state, $config);
    }

    $config->save();
  }

}
