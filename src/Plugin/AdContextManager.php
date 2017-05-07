<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the manager for Advertising context plugins and collected data.
 */
class AdContextManager extends DefaultPluginManager {

  /**
   * An array holding a collection of backend context data.
   *
   * Various implementations of Advertising types may
   * apply the collected context data via backend.
   *
   * @var array
   */
  protected $contextData;

  /**
   * Constructor method.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ad_entity/AdContext', $namespaces, $module_handler, 'Drupal\ad_entity\Plugin\AdContextInterface', 'Drupal\ad_entity\Annotation\AdContext');
    $this->alterInfo('ad_entity_adcontext');
    $this->setCacheBackend($cache_backend, 'ad_entity_adcontext');
    $this->setContextData([]);
  }

  /**
   * Adds backend context data to the current data collection.
   *
   * @param string $plugin_id
   *   The plugin id of the context.
   * @param array $settings
   *   (Optional) An array of corresponding settings for the context.
   * @param array $apply_on
   *   (Optional) An array of Advertising entity ids where to apply the context.
   *   When empty, the context may be applied on all available ads.
   */
  public function addContextData($plugin_id, array $settings = [], array $apply_on = []) {
    $this->contextData[$plugin_id][] = [
      'settings' => $settings,
      'apply_on' => $apply_on,
    ];
  }

  /**
   * Returns a list of backend context data for the given Advertising entity id.
   *
   * @param string $ad_entity_id
   *   The id (machine name) of the Advertising entity.
   *
   * @return array
   *   The list of available backend context data for the Advertising entity.
   */
  public function getContextDataForEntity($ad_entity_id) {
    $available = [];

    foreach ($this->contextData as $plugin_id => $data_items) {
      foreach ($data_items as $data) {
        if (empty($data['apply_on']) || in_array($ad_entity_id, $data['apply_on'])) {
          $available[$plugin_id][] = $data['settings'];
        }
      }
    }

    return $available;
  }

  /**
   * Returns a list of backend context data belonging to the context plugin id.
   *
   * @param string $plugin_id
   *   The context plugin id.
   *
   * @return array
   *   The list of backend context data belonging to the context plugin.
   */
  public function getContextDataForPlugin($plugin_id) {
    if (!empty($this->contextData[$plugin_id])) {
      return $this->contextData[$plugin_id];
    }
    return [];
  }

  /**
   * Returns a list of context data for given plugin and Advertising entity id.
   *
   * @param string $plugin_id
   *   The context plugin id.
   * @param string $ad_entity_id
   *   The id (machine name) of the Advertising entity.
   *
   * @return array
   *   The list of available context data for the plugin and Advertising entity.
   */
  public function getContextDataForPluginAndEntity($plugin_id, $ad_entity_id) {
    $available = [];

    if (!empty($this->contextData[$plugin_id])) {
      foreach ($this->contextData[$plugin_id] as $data) {
        if (empty($data['apply_on']) || in_array($ad_entity_id, $data['apply_on'])) {
          $available[] = $data['settings'];
        }
      }
    }

    return $available;
  }

  /**
   * Get the whole collection of backend context data.
   *
   * @return array
   *   The current backend context data collection.
   */
  public function getContextData() {
    return $this->contextData;
  }

  /**
   * Set the current collecton of backend context data.
   *
   * @param array $context_data
   *   The backend context data collection.
   */
  public function setContextData(array $context_data) {
    $this->contextData = $context_data;
  }

}
