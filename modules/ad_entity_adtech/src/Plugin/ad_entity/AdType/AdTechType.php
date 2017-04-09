<?php

namespace Drupal\ad_entity_adtech\Plugin\ad_entity\AdType;

use Drupal\ad_entity\Plugin\AdTypeBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Type plugin for AdTech Factory advertisement.
 *
 * @AdType(
 *   id = "adtech_factory",
 *   label = "AdTech Factory"
 * )
 */
class AdTechType extends AdTypeBase {

  public function globalSettingsForm(array $form, FormStateInterface $form_state, Config $config) {
    return parent::globalSettingsForm($form, $form_state, $config);
  }

}
