<?php

namespace Drupal\backup_migrate\Core\Source;

/**
 * Interface DatabaseSourceInterface.
 *
 * @package Drupal\backup_migrate\Core\Source
 */
interface DatabaseSourceInterface extends SourceInterface {

  /**
   * Get a list of tables in this source.
   */
  public function getTableNames();

  /**
   * Get an array of tables with some info.
   *
   * Each entry must have at least a 'name' key containing the table name.
   *
   * @return array
   */
  public function getTables();

}
