<?php

namespace Drupal\ad_entity\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Plugin implementation of the 'ad_entity_context' field type.
 *
 * @FieldType(
 *   id = "ad_entity_context",
 *   label = @Translation("Advertising context"),
 *   description = @Translation("Contextual settings for Advertising entities being shown on the site"),
 *   default_widget = "ad_entity_context",
 *   default_formatter = "ad_entity_context"
 * )
 */
class ContextItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // TODO Create typed data definition for context instances.
    $properties['context'] = MapDataDefinition::create('map')
      ->setLabel(new TranslatableMarkup('Context'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'context' => [
          'type' => 'blob',
          'size' => 'big',
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $context = $this->get('context')->getValue();
    return empty($context) ? TRUE : FALSE;
  }

}
