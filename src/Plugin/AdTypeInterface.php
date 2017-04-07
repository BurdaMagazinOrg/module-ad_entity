<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the plugin interface for Advertising types.
 */
interface AdTypeInterface extends PluginInspectionInterface {

  /**
   * Returns a list of allowed View handler plugins.
   *
   * @param array $form
   *   The form which is being used to select the list of allowed view handlers.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The corresponding form state.
   *
   * @return array
   *   An array containing the view plugin Ids as values.
   */
  public function allowedViews(array $form, FormStateInterface $form_state);

}
