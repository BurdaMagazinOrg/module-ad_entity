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
    $element = $this->includeForAppliance($items);
    foreach ($this->termStorage->loadAllParents($items->getEntity()->id()) as $parent) {
      $this->renderer->addCacheableDependency($element, $parent);
    }
    if ($items->isEmpty()) {
      $override_items = $this->getOverrideItems($items);
      if (!$override_items->isEmpty()) {
        $element = array_merge($element, $this->includeForAppliance($override_items));
      }
    }
    return $element;
  }

}
