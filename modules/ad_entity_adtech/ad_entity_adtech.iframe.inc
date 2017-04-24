<?php

/**
 * @file
 * Advertising Entity: AdTech Factory iFrame implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;

/**
 * Preprocess implementation for an AdTech Factory tag as iFrame.
 *
 * @param array &$variables
 *   An array of available variables.
 */
function template_preprocess_adtech_iframe(array &$variables) {
  \Drupal::moduleHandler()
    ->loadInclude('ad_entity_adtech', 'inc', 'ad_entity_adtech.theme');
  template_preprocess_adtech_default($variables);

  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  $settings = $ad_entity->getThirdPartySettings('ad_entity_adtech');

  if (!empty($settings['iframe'])) {
    $variables['iframe'] = $settings['iframe'];
  }

  if ($config = \Drupal::config('ad_entity.settings')) {
    $settings = $config->get('adtech_factory');
    if (!empty($settings['library_source'])) {
      $variables['library_source'] = $settings['library_source'];
    }
  }
}
