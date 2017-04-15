<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'ad_entity_tree_aggregation_context' formatter.
 *
 * @FieldFormatter(
 *   id = "ad_entity_tree_aggregation_context",
 *   label = @Translation("Aggregated context from taxonomy tree"),
 *   field_types = {
 *     "ad_entity_context"
 *   }
 * )
 */
class TreeAggregationContextFormatter extends ContextFormatterBase {

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

    // @todo Display the contexts, either via drupalSettings
    // or via DOM application/json script tags.
    // No need to create instances of context plugins,
    // because the definition already contains
    // the only needed JS library to use.
    return $element;
  }

}
