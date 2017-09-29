<?php

namespace Drupal\ad_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines Display configurations for Advertisement.
 *
 * @ConfigEntityType(
 *   id = "ad_display",
 *   label = @Translation("Display for Advertisement"),
 *   label_collection = @Translation("Display configs for Advertisement"),
 *   label_singular = @Translation("Display for Advertisement"),
 *   label_plural = @Translation("Display configs for Advertisement"),
 *   handlers = {
 *     "list_builder" = "Drupal\ad_entity\AdDisplayListBuilder",
 *     "view_builder" = "Drupal\ad_entity\AdDisplayViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\ad_entity\Form\AdDisplayForm",
 *       "edit" = "Drupal\ad_entity\Form\AdDisplayForm",
 *       "delete" = "Drupal\ad_entity\Form\AdDisplayDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ad_entity\AdDisplayHtmlRouteProvider",
 *     },
 *    "access" = "Drupal\entity\EntityAccessControlHandler",
 *    "permission_provider" = "Drupal\entity\EntityPermissionProvider"
 *   },
 *   config_prefix = "ad_entity.display",
 *   admin_permission = "administer ad_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/ad_entity/display/{ad_display}",
 *     "add-form" = "/admin/structure/ad_entity/display/add",
 *     "edit-form" = "/admin/structure/ad_entity/display/{ad_display}/edit",
 *     "delete-form" = "/admin/structure/ad_entity/display/{ad_display}/delete",
 *     "collection" = "/admin/structure/ad_entity/display"
 *   }
 * )
 */
class AdDisplay extends ConfigEntityBase implements AdDisplayInterface {

  /**
   * The display ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The display label.
   *
   * @var string
   */
  protected $label;

}
