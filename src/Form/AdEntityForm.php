<?php

namespace Drupal\ad_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdEntityForm.
 *
 * @package Drupal\ad_entity\Form
 */
class AdEntityForm extends EntityForm {

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
