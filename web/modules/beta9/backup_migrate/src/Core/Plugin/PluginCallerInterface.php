<?php

namespace Drupal\backup_migrate\Core\Plugin;

/**
 * For plugins which must have access to a plugin manager.
 *
 * .. because they need to access other plugins.
 *
 * @package Drupal\backup_migrate\Core\Plugin
 */
interface PluginCallerInterface {

  /**
   * Inject the plugin manager.
   *
   * @param \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface $plugins
   */
  public function setPluginManager(PluginManagerInterface $plugins);

  /**
   * Get the plugin manager.
   *
   * @return \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface
   */
  public function plugins();

}
