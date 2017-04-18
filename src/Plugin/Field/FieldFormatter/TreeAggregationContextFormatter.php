<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'tree_aggregation_context' formatter.
 *
 * @FieldFormatter(
 *   id = "tree_aggregation_context",
 *   label = @Translation("Context with tree aggregation"),
 *   field_types = {
 *     "ad_entity_context"
 *   }
 * )
 */
class TreeAggregationContextFormatter extends TaxonomyContextFormatterBase {

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

    $field_name = $items->getFieldDefinition()->get('field_name');
    $aggregated_items = [];
    // ::loadAllParents() already includes the term itself.
    $parents = $this->termStorage->loadAllParents($items->getEntity()->id());
    foreach ($parents as $parent) {
      if ($parent_items = $parent->get($field_name)) {
        $aggregated_items[] = $parent_items;
      }
    }

    foreach ($aggregated_items as $items) {
      foreach ($items as $item) {
        $element[] = $this->buildElementFromItem($item);
      }
    }

    return $element;
  }

}
