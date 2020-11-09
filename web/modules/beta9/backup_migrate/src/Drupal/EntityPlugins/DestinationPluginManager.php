<?php

namespace Drupal\backup_migrate\Drupal\EntityPlugins;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\Destination
 */
class DestinationPluginManager extends DefaultPluginManager {

  /**
   * Constructs a DestinationPluginManager object.
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
    parent::__construct('Plugin/BackupMigrateDestination', $namespaces, $module_handler, 'Drupal\backup_migrate\Drupal\EntityPlugins\DestinationPluginInterface', 'Drupal\backup_migrate\Drupal\EntityPlugins\Annotation\BackupMigrateDestinationPlugin');
    $this->alterInfo('backup_migrate_destination_info');
    $this->setCacheBackend($cache_backend, 'backup_migrate_destination_plugins');
  }

}
