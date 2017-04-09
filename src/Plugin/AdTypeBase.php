<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;

/**
 * Base class for Advertising types.
 */
abstract class AdTypeBase extends PluginBase implements AdTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function globalSettingsForm(array $form, FormStateInterface $form_state, Config $config) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function globalSettingsValidate(array &$form, FormStateInterface $form_state, Config $config) {}

  /**
   * {@inheritdoc}
   */
  public function globalSettingsSubmit(array &$form, FormStateInterface $form_state, Config $config) {}

}
