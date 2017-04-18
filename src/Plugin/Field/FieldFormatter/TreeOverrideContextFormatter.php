<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'tree_override_context' formatter.
 *
 * @FieldFormatter(
 *   id = "tree_override_context",
 *   label = @Translation("Context with tree override"),
 *   field_types = {
 *     "ad_entity_context"
 *   }
 * )
 */
class TreeOverrideContextFormatter extends TaxonomyContextFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    switch ($field_definition->getTargetEntityTypeId()) {
      case 'taxonomy_term':
        return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $override_items = $this->getOverrideItems($items);

    foreach ($override_items as $item) {
      $element[] = $this->buildElementFromItem($item);
    }

    return $element;
  }

}
