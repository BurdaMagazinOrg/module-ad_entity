<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;

/**
 * Defines the plugin interface for Advertising types.
 */
interface AdTypeInterface extends PluginInspectionInterface {

  /**
   * Returns the form elements for global settings.
   *
   * @param array $form
   *   The global settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The corresponding form state.
   * @param \Drupal\Core\Config\Config $config
   *   The configuration object.
   *
   * @return array
   *   The settings as form array.
   */
  public function globalSettingsForm(array $form, FormStateInterface $form_state, Config $config);

  /**
   * Validate the global settings form.
   *
   * @param array &$form
   *   The global settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The corresponding form state.
   * @param \Drupal\Core\Config\Config $config
   *   The configuration object.
   */
  public function globalSettingsValidate(array &$form, FormStateInterface $form_state, Config $config);

  /**
   * Act on form submission of global settings.
   *
   * @param array &$form
   *   The global settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The corresponding form state.
   * @param \Drupal\Core\Config\Config $config
   *   The configuration object.
   */
  public function globalSettingsSubmit(array &$form, FormStateInterface $form_state, Config $config);

}
