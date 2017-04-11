<?php

namespace Drupal\ad_entity\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

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
    $properties['value'] = DataDefinition::create('ad_entity.context')
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
        'value' => [
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
    $value = $this->get('value')->getValue();
    return $value ? TRUE : FALSE;
  }

}
