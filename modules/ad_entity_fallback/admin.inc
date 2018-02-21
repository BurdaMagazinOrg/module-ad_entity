<?php

/**
 * @file
 * Advertising Entity: Fallback - functions for admin pages.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Adds fallback options to the Advertising entity form.
 *
 * @param array &$form
 *   The form array of the Advertising entity form.
 * @param FormStateInterface $form_state
 *   The corresponding form state.
 */
function _ad_entity_fallback_form(array &$form, FormStateInterface $form_state) {
  $ad_entities = \Drupal::entityTypeManager()->getStorage('ad_entity')->loadMultiple();
  $options = [];
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  foreach ($ad_entities as $ad_entity) {
    $has_fallback = (bool) $ad_entity->getThirdPartySetting('ad_entity_fallback', 'ad_entity_id');
    if (!$has_fallback) {
      $options[$ad_entity->id()] = $ad_entity->label();
    }
  }

  /** @var \Drupal\Core\Entity\EntityForm $form_object */
  $form_object = $form_state->getFormObject();
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $current */
  $current = $form_object->getEntity();
  if (empty($current)) {
    return;
  }
  if (!$current->isNew()) {
    unset($options[$current->id()]);
  }
  if (empty($options)) {
    return;
  }
  $default = $current->getThirdPartySettings('ad_entity_fallback');
  $form['third_party']['fallback'] = [
    '#type' => 'fieldset',
    '#tree' => TRUE,
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
    '#attributes' => ['id' => 'fallback-form-settings'],
    '#title' => t("Fallback"),
    '#weight' => 100,
    '#parents' => ['third_party_settings', 'ad_entity_fallback'],
  ];
  $form['third_party']['fallback']['ad_entity_id'] = [
    '#type' => 'select',
    '#options' => $options,
    '#title' => t('Advertising entity'),
    '#description' => t('The Advertising entity to use as fallback when the current entity is empty.'),
    '#default_value' => !empty($default['ad_entity_id']) ? $default['ad_entity_id'] : '',
    '#required' => FALSE,
    '#empty_value' => '',
  ];
}
