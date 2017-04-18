<?php

/**
 * Implements hook_drush_command().
 */
function ad_entity_ad_integration_drush_command() {
  $commands = [];

  // Check all translations for available updates.
  $commands['shovel-to-adtech-targeting'] = [
    'description' => 'Shovels field values from the ad_integration field to the ad_entity context field. Converts the Ad Integration values to key-value pairs for AdTech Factory targeting.',
    'arguments' => [
      'entity_type' => "The affected entity type, either 'node' or 'taxonomy_term'",
      'ad_integration_field' => "Machine name of the entity's ad_integration field.",
      'ad_entity_field' => "Machine name of the entity's ad_entity field. Make sure you have attached this field to the entity type before.",
    ],
  ];

  return $commands;
}

/**
 * Shovels field values from the ad_integration field to the ad_entity field.
 *
 * Converts the Ad Integration values to
 * key-value pairs of AdTech Factory targeting.
 *
 * @param string $entity_type
 *   The entity type, e.g. node or taxonomy_term
 * @param string $ad_integration_field
 *   Machine name of the entity's ad_integration field.
 * @param string $ad_entity_field
 *   Machine name of the entity's ad_entity field.
 */
function drush_ad_entity_ad_integration_shovel_to_adtech_targeting($entity_type, $ad_integration_field, $ad_entity_field) {
  $db = \Drupal::database();
  if (!in_array($entity_type, ['node', 'taxonomy_term'])
  || !isset($entity_type, $ad_integration_field, $ad_entity_field)) {
    drush_log('Invalid arguments supplied, aborting.', 'error');
    return;
  }

  /**
   * @todo 1. Load Ad Integration config, extract default values.
   * 2. Extract all datasets from table, replace NULL values with default ones.
   * Alternative: Extract single dataset from table to prevent memory overflow.
   * 3. Insert for each row a new dataset in ad_entity context field.
   * Maybe a sleep statement is required to prevent database overload.
   */
}
