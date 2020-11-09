<?php

namespace Drupal\backup_migrate\Drupal\EntityPlugins;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\Source
 */
class SourcePluginManager extends DefaultPluginManager {

  /**
   * Constructs a SourcePluginManager object.
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
    parent::__construct('Plugin/BackupMigrateSource', $namespaces, $module_handler, 'Drupal\backup_migrate\Drupal\EntityPlugins\SourcePluginInterface', 'Drupal\backup_migrate\Drupal\EntityPlugins\Annotation\BackupMigrateSourcePlugin');
    $this->alterInfo('backup_migrate_source_info');
    $this->setCacheBackend($cache_backend, 'backup_migrate_source_plugins');
  }

}
