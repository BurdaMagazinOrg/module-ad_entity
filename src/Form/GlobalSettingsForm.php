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
   * The context form element builder.
   *
   * @var \Drupal\ad_entity\Form\AdContextElementBuilder
   */
  protected $contextElementBuilder;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\ad_entity\Plugin\AdTypeManager $ad_type_manager
   *   The Advertising type manager.
   * @param \Drupal\ad_entity\Form\AdContextElementBuilder
   *   The context form element builder.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AdTypeManager $ad_type_manager, AdContextElementBuilder $context_element_builder) {
    parent::__construct($config_factory);
    $this->typeManager = $ad_type_manager;
    $this->contextElementBuilder = $context_element_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $context_element_builder = AdContextElementBuilder::create($container);
    return new static(
      $container->get('config.factory'),
      $container->get('ad_entity.type_manager'),
      $context_element_builder
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

    $form['common']['site_wide_context'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Site wide context'),
      '#attributes' => ['id' => 'edit-site-wide-context'],
      '#weight' => 30,
    ];
    $form['common']['site_wide_context']['info'] = [
      '#prefix' => '<div class="description">',
      '#suffix' => '</div>',
      '#markup' => $this->t('Advertising context, which has been defined here, will be applied on every Advertising entity being displayed on the website.'),
      '#weight' => 10,
    ];
    $context_values = $form_state->getValue('site_wide_context', []);
    $site_wide_context = [];
    if (empty($context_values)) {
      // No form submission values given, use config as default values.
      $site_wide_context = $config->get('site_wide_context');
    }
    else {
      // Map form submission values to context data.
      foreach ($context_values as $i => $context_value) {
        $site_wide_context[] = [
          'plugin_id' => $context_value['context']['context_plugin_id'],
          'settings' => $context_value['context']['context_settings'],
          'apply_on' => $context_value['context']['apply_on'],
        ];
      }
    }
    $triggered_add_more = FALSE;
    if ($triggering_element = $form_state->getTriggeringElement()) {
      if (!empty($triggering_element['#name']) && $triggering_element['#name'] == 'add_context') {
        $triggered_add_more = TRUE;
      }
    }
    // Provide at least one empty field,
    // or add another item in case the user triggered so.
    if (empty($site_wide_context) || $triggered_add_more) {
      $site_wide_context[] = [
        'plugin_id' => '',
        'settings' => [],
        'apply_on' => [],
      ];
    }
    foreach ($site_wide_context as $i => $context_data) {
      $this->contextElementBuilder->clearValues()
        ->setContextPluginValue($context_data['plugin_id'])
        ->setContextApplyOnValue($context_data['apply_on'])
        ->setContextSettingsValue($context_data['plugin_id'], $context_data['settings']);
      $context_form_element = [
        '#parents' => ['site_wide_context', $i],
        '#weight' => ($i + 1) * 10,
      ];
      $context_form_element = $this->contextElementBuilder->buildElement($context_form_element, $form, $form_state);
      $form['common']['site_wide_context'][$i] = $context_form_element;
    }
    $form['common']['site_wide_context']['more'] = [
      '#type' => 'button',
      '#name' => 'add_context',
      '#value' => $this->t("Add another item"),
      '#weight' => (count($site_wide_context) + 1) * 10,
      '#ajax' => [
        'callback' => [$this, 'addContextElement'],
        'wrapper' => 'edit-site-wide-context',
        'effect' => 'fade',
        'method' => 'replace',
        'progress' => [
          'type' => 'throbber',
          'message' => '',
        ],
      ],
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
   * Submit handler to add another context form element.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array part, usually inserted via AJAX.
   */
  public function addContextElement(array &$form, FormStateInterface $form_state) {
    return $form['common']['site_wide_context'];
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

    $context_values = $form_state->getValue('site_wide_context');
    $context_data = [];
    foreach ($context_values as $i => $context_value) {
      $context_value = $this->contextElementBuilder->massageFormValues($context_value);
      if (!empty($context_value['context']['context_plugin_id'])) {
        $plugin_id = $context_value['context']['context_plugin_id'];
        $context_settings = $context_value['context']['context_settings'][$plugin_id];
        $context_data[] = [
          'plugin_id' => $plugin_id,
          'settings' => $context_settings,
          'apply_on' => $context_value['context']['apply_on'],
        ];
      }
    }
    $config->set('site_wide_context', $context_data);

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
