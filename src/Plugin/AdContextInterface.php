<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * Defines the interface for Advertising context plugins.
 */
interface AdContextInterface extends PluginInspectionInterface {

  /**
   * Returns the form elements for the settings of a given context item.
   *
   * @param array $plugin_settings
   *   The current values of the settings for the given context item.
   * @param \Drupal\Core\TypedData\Plugin\DataType\Map $context_item
   *   The context item.
   * @param array $form
   *   The form where the given context item is being configured.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The corresponding form state.
   *
   * @return array
   *   The settings as form array.
   */
  public function settingsForm(array $plugin_settings, Map $context_item, array $form, FormStateInterface $form_state);

  /**
   * Massages the form values of the settings into a proper storage format.
   *
   * The settings must represent a JSON-compatible data structure,
   * since these will be used as settings output for the context itself.
   *
   * @param array $plugin_settings
   *   The submitted form values of the plugin settings.
   *
   * @return array
   *   The plugin settings, ready to be saved on the storage.
   */
  public function massageSettings(array $plugin_settings);

}
