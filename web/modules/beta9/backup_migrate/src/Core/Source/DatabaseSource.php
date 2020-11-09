<?php

namespace Drupal\backup_migrate\Core\Source;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorTrait;
use Drupal\backup_migrate\Core\Plugin\PluginBase;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Source
 */
abstract class DatabaseSource extends PluginBase implements DatabaseSourceInterface, FileProcessorInterface {
  use FileProcessorTrait;

  /**
   * Get a definition for user-configurable settings.
   *
   * @param array $params
   *
   * @return array
   */
  public function configSchema(array $params = []) {
    $schema = [];

    // Init settings.
    if ($params['operation'] == 'initialize') {
      $schema['fields']['host'] = [
        'type' => 'text',
        'title' => 'Hostname',
      ];
      $schema['fields']['database'] = [
        'type' => 'text',
        'title' => 'Database',
      ];
      $schema['fields']['username'] = [
        'type' => 'text',
        'title' => 'Username',
      ];
      $schema['fields']['password'] = [
        'type' => 'password',
        'title' => 'Password',
      ];
      $schema['fields']['port'] = [
        'type' => 'number',
        'min' => 1,
        'max' => 65535,
        'title' => 'Port',
      ];
    }

    return $schema;
  }

  /**
   * Get the default values for the plugin.
   *
   * @return \Drupal\backup_migrate\Core\Config\Config
   */
  public function configDefaults() {
    return new Config([
      'generator' => 'Backup and Migrate Core',
    ]);
  }

  /**
   * Get a list of tables in this source.
   */
  public function getTableNames() {
    try {
      return $this->getRawTableNames();
    }
    catch (\Exception $e) {
      // Todo: Log this exception.
      return [];
    }
  }

  /**
   * Get an array of tables with some info.
   *
   * Each entry must have at least a 'name' key containing the table name.
   *
   * @return array
   */
  public function getTables() {
    try {
      return $this->getRawTables();
    }
    catch (\Exception $e) {
      // Todo: Log this exception.
      return [];
    }
  }

  /**
   * Get the list of tables from this db.
   *
   * @return array
   */
  protected function getRawTableNames() {
    $out = [];
    foreach ($this->getRawTables() as $table) {
      $out[$table['name']] = $table['name'];
    }
    return $out;
  }

  /**
   * Internal overridable function to actually generate table info.
   *
   * @return array
   */
  abstract protected function getRawTables();

}
