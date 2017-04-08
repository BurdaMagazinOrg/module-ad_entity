<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Advertising types.
 */
abstract class AdTypeBase extends PluginBase implements AdTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function globalSettingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function globalSettingsValidate(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function globalSettingsSubmit(array &$form, FormStateInterface $form_state) {}

}
