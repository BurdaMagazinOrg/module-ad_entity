<?php

namespace Drupal\ad_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ad_entity\Plugin\AdTypeManager;
use Drupal\ad_entity\Plugin\AdViewManager;

/**
 * Class AdEntityForm.
 *
 * @package Drupal\ad_entity\Form
 */
class AdEntityForm extends EntityForm {

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
  protected $typeManager;

  /**
   * The Advertising view manager.
   *
   * @var \Drupal\ad_entity\Plugin\AdViewManager
   */
  protected $viewManager;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\ad_entity\Plugin\AdTypeManager $ad_type_manager
   *   The Advertising type manager.
   * @param \Drupal\ad_entity\Plugin\AdViewManager $ad_type_manager
   *   The Advertising view manager.
   */
  public function __construct(FormBuilderInterface $form_builder, AdTypeManager $ad_type_manager, AdViewManager $ad_view_manager) {
    $this->formBuilder = $form_builder;
    $this->typeManager = $ad_type_manager;
    $this->viewManager = $ad_view_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('ad_entity.type_manager'),
      $container->get('ad_entity.view_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $type_ids = array_keys($this->typeManager->getDefinitions());
    if (empty($type_ids)) {
      return [
        '#markup' => $this->t('For being able to create Advertising entities, you need to install some Advertising plugins first.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $ad_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $ad_entity->label(),
      '#description' => $this->t("Label for the Advertising entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $ad_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ad_entity\Entity\AdEntity::load',
      ],
      '#disabled' => !$ad_entity->isNew(),
    ];

    // TODO Fieldset for type, fieldset for view settings (when multiple views are allowed).
    $type_definitions = $this->typeManager->getDefinitions();
    $options = [];
    foreach ($type_definitions as $id => $definition) {
      $options[$id] = $definition['label'];
    }
    $form['type_plugin_id'] = [
      '#type' => 'select',
      '#title' => $this->t("Advertising type"),
      '#options' => $options,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $ad_entity = $this->entity;
    $status = $ad_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Advertising entity.', [
          '%label' => $ad_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Advertising entity.', [
          '%label' => $ad_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($ad_entity->toUrl('collection'));
  }

}
