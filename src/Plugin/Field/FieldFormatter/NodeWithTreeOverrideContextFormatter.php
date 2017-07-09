<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'node_with_tree_override_context' formatter.
 *
 * @FieldFormatter(
 *   id = "node_with_tree_override_context",
 *   label = @Translation("Context from node with taxonomy (tree override)"),
 *   field_types = {
 *     "ad_entity_context"
 *   }
 * )
 */
class NodeWithTreeOverrideContextFormatter extends TaxonomyContextFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    switch ($field_definition->getTargetEntityTypeId()) {
      case 'node':
        return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $aggregated_items = [$items];
    /** @var \Drupal\taxonomy\TermInterface $term */
    foreach ($this->getTermsForNode($items->getEntity()->id()) as $term) {
      $field_definitions = $term->getFieldDefinitions();
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $definition */
      foreach ($field_definitions as $definition) {
        if ($definition->getType() == 'ad_entity_context') {
          foreach ($this->termStorage->loadAllParents($term->id()) as $parent) {
            $this->renderer->addCacheableDependency($element, $parent);
          }
          $field_name = $definition->getName();
          if ($term_items = $term->get($field_name)) {
            $aggregated_items[] = $term_items;
            if ($term_items->isEmpty()) {
              $override_items = $this->getOverrideItems($term_items);
              if (!$override_items->isEmpty()) {
                $aggregated_items[] = $this->getOverrideItems($term_items);
              }
            }
          }
        }
      }
    }

    foreach ($aggregated_items as $to_include) {
      $element = array_merge($element, $this->includeForAppliance($to_include));
    }

    return $element;
  }

}
