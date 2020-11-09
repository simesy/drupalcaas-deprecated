<?php

namespace Drupal\backup_migrate\Core\Filter;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\Plugin\PluginManager;
use Drupal\backup_migrate\Core\Source\DatabaseSourceInterface;

/**
 * Allows the exclusion of certain data from a database.
 *
 * @package Drupal\backup_migrate\Core\Filter
 */
class DBExcludeFilter extends PluginBase {

  /**
   * @var \Drupal\backup_migrate\Core\Plugin\PluginManager
   */
  protected $sourceManager;

  /**
   * The 'beforeDbTableBackup' plugin op.
   *
   * @param array $table
   * @param array $params
   *
   * @return array $table
   */
  public function beforeDbTableBackup(array $table, array $params = []) {
    $exclude = $this->confGet('exclude_tables');
    $nodata = $this->confGet('nodata_tables');
    if (in_array($table['name'], $exclude)) {
      $table['exclude'] = TRUE;
    }
    if (in_array($table['name'], $nodata)) {
      $table['nodata'] = TRUE;
    }
    return $table;
  }

  /**
   * Get the default values for the plugin.
   *
   * @return \Drupal\backup_migrate\Core\Config\Config
   */
  public function configDefaults() {
    return new Config([
      'source' => '',
      'exclude_tables' => [],
      'nodata_tables' => [],
    ]);
  }

  /**
   * Get a definition for user-configurable settings.
   *
   * @param array $params
   *
   * @return array
   */
  public function configSchema(array $params = []) {
    $schema = [];

    if ($params['operation'] == 'backup') {
      $tables = [];

      foreach ($this->sources()->getAll() as $source_key => $source) {
        if ($source instanceof DatabaseSourceInterface) {
          $tables += $source->getTableNames();
        }

        if ($tables) {
          // Backup settings.
          $schema['groups']['default'] = [
            'title' => $this->t('Exclude database tables'),
          ];

          $table_select = [
            'type' => 'enum',
            'multiple' => TRUE,
            'options' => $tables,
            'actions' => ['backup'],
            'group' => 'default',
          ];
          $schema['fields']['exclude_tables'] = $table_select + [
            'title' => $this->t('Exclude these tables entirely'),
          ];

          $schema['fields']['nodata_tables'] = $table_select + [
            'title' => $this->t('Exclude data from these tables'),
          ];

        }
      }
    }
    return $schema;
  }

  /**
   * @return \Drupal\backup_migrate\Core\Plugin\PluginManager
   */
  public function sources() {
    return $this->sourceManager ? $this->sourceManager : new PluginManager();
  }

  /**
   * @param \Drupal\backup_migrate\Core\Plugin\PluginManager $sourceManager
   */
  public function setSourceManager(PluginManager $sourceManager) {
    $this->sourceManager = $sourceManager;
  }

}
