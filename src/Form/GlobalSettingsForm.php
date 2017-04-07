<?php

namespace Drupal\ad_entity\Form;

use Drupal\Core\Form\FormBuilder;
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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The Advertising type manager.
   *
   * @var \Drupal\ad_entity\Plugin\AdTypeManager
   */
  protected $adTypeManager;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The form builder.
   * @param \Drupal\ad_entity\Plugin\AdTypeManager $ad_type_manager
   *   The Advertising type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FormBuilder $form_builder, AdTypeManager $ad_type_manager) {
    parent::__construct($config_factory);
    $this->formBuilder = $form_builder;
    $this->adTypeManager = $ad_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('form_builder'),
      $container->get('ad_entity.type_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ad_entity.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ad_entity_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ad_entity.settings');
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ad_entity.settings')->save();
  }

}
