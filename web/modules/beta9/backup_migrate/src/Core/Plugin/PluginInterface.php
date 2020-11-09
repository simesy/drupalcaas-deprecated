<?php

namespace Drupal\backup_migrate\Core\Plugin;

/**
 * An interface to describe a Backup and Migrate plugin.
 *
 * Plugins take care of all elements of the backup process and can be configured
 * externally.
 *
 * All of the work is done in plugins. Therefore they may need injected:.
 *
 * Sources
 * Destinations
 * Other Plugins?
 * Config
 * Application
 *  Cache
 *  State
 * TempFileManager
 *  TempFileAdapter.
 */
interface PluginInterface {

  /**
   * Get a list of supported operations and their weight.
   *
   * An array of operations should take the form:
   *
   * [
   *  'backup' => ['weight' => 100],
   *  'restore' => ['weight' => -100],
   * ];
   *
   * @return array
   */
  public function supportedOps();

  /**
   * Does this plugin implement the given operation.
   *
   * @param string $op
   *   The name of the operation.
   *
   * @return bool
   */
  public function supportsOp($op);

  /**
   * What is the weight of the given operation for this plugin.
   *
   * @param string $op
   *   The name of the operation.
   *
   * @return int
   */
  public function opWeight($op);

}
