<?php

namespace Drupal\ad_entity\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the manager for Advertising context plugins.
 */
class AdContextManager extends DefaultPluginManager {

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
  }

}
