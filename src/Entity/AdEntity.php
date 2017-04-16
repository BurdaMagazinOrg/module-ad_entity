<?php

namespace Drupal\ad_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Advertising entity.
 *
 * @ConfigEntityType(
 *   id = "ad_entity",
 *   label = @Translation("Advertising entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\ad_entity\AdEntityListBuilder",
 *     "view_builder" = "Drupal\ad_entity\AdEntityViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\ad_entity\Form\AdEntityForm",
 *       "edit" = "Drupal\ad_entity\Form\AdEntityForm",
 *       "delete" = "Drupal\ad_entity\Form\AdEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ad_entity\AdEntityHtmlRouteProvider",
 *     },
 *    "access" = "Drupal\entity\EntityAccessControlHandler",
 *    "permission_provider" = "Drupal\entity\EntityPermissionProvider"
 *   },
 *   config_prefix = "ad_entity",
 *   admin_permission = "administer advertising entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/ad_entity/{ad_entity}",
 *     "add-form" = "/admin/structure/ad_entity/add",
 *     "edit-form" = "/admin/structure/ad_entity/{ad_entity}/edit",
 *     "delete-form" = "/admin/structure/ad_entity/{ad_entity}/delete",
 *     "collection" = "/admin/structure/ad_entity"
 *   }
 * )
 */
class AdEntity extends ConfigEntityBase implements AdEntityInterface {

  /**
   * The Advertising entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Advertising entity label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Advertising view manager.
   *
   * @var \Drupal\ad_entity\Plugin\AdViewManager
   */
  protected $viewManager;

  /**
   * An instance of the view handler plugin.
   *
   * @var \Drupal\ad_entity\Plugin\AdViewInterface
   */
  protected $viewPlugin;

  /**
   * An instance of the type plugin.
   *
   * @var \Drupal\ad_entity\Plugin\AdTypeInterface
   */
  protected $typePlugin;

  /**
   * AdEntity constructor.
   *
   * @param array $values
   *   The values as array.
   * @param string $entity_type
   *   The entity type as string.
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->viewManager = \Drupal::service('ad_entity.view_manager');
  }

  /**
   * {@inheritdoc}
   */
  public function getViewPlugin() {
    if (!isset($this->viewPlugin)) {
      $id = $this->get('view_plugin_id');
      $this->viewPlugin = ($id && $this->viewManager->hasDefinition($id)) ?
        $this->viewManager->createInstance($id) : NULL;
    }
    return $this->viewPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypePlugin() {
    if (!isset($this->typePlugin)) {
      $id = $this->get('type_plugin_id');
      // Use the type manager only when it's really needed.
      $type_manager = \Drupal::service('ad_entity.type_manager');
      $this->typePlugin = ($id && $type_manager->hasDefinition($id)) ?
        $type_manager->createInstance($id) : NULL;
    }
    return $this->typePlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // Make sure the provider of the view plugin is given as a dependency.
    // The type plugin however usually provides third party settings,
    // which implies that its provider is already added as dependency.
    $view_id = $this->get('view_plugin_id');
    if ($view_id && $this->viewManager->hasDefinition($view_id)) {
      $definition = $this->viewManager->getDefinition($view_id);
      if (!empty($definition['provider'])) {
        $this->addDependency('module', $definition['provider']);
      }
    }
    return parent::calculateDependencies();
  }

}
