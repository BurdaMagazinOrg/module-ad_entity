<?php

namespace Drupal\ad_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdDisplayForm.
 *
 * @package Drupal\ad_entity\Form
 */
class AdDisplayForm extends EntityForm {

  /**
   * Constructor method.
   */
  public function __construct() {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // TODO check if there are any Advertising entites existent. If not, show message.
    $entities = [];
    if (empty($entities)) {
      return [
        '#markup' => $this->t('For being able to create a Display config for Advertisement, you need to create at least one Advertising entity first.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // TODO validate
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // TODO submit
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
