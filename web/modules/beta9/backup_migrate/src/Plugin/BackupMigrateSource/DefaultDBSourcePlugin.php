<?php

namespace Drupal\backup_migrate\Plugin\BackupMigrateSource;

use Drupal\Core\Database\Database;
use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Filter\DBExcludeFilter;
use Drupal\backup_migrate\Core\Main\BackupMigrateInterface;
use Drupal\backup_migrate\Drupal\Source\DrupalMySQLiSource;
use Drupal\backup_migrate\Drupal\EntityPlugins\SourcePluginBase;

/**
 * Defines an default database source plugin.
 *
 * @BackupMigrateSourcePlugin(
 *   id = "DefaultDB",
 *   title = @Translation("Default Database"),
 *   description = @Translation("Back up the Drupal database."),
 *   locked = true
 * )
 */
class DefaultDBSourcePlugin extends SourcePluginBase {

  /**
   * Get the Backup and Migrate plugin object.
   *
   * @return Drupal\backup_migrate\Core\Plugin\PluginInterface
   */
  public function getObject() {
    // Add the default database.
    $info = Database::getConnectionInfo('default', 'default');
    $info = $info['default'];

    // Set a default port if none is set. Because that's what core does.
    $info['port'] = (empty($info['port']) ? 3306 : $info['port']);
    if ($info['driver'] == 'mysql') {
      $conf = $this->getConfig();
      foreach ($info as $key => $value) {
        $conf->set($key, $value);
      }
      return new DrupalMySQLiSource($conf);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBackupMigrate(BackupMigrateInterface $bam, $key, $options = []) {
    if ($source = $this->getObject()) {
      $bam->sources()->add($key, $source);
      // @todo This needs a better solution.
      $config = [
        'exclude_tables' => [],
        'nodata_tables' => [
          'cache_advagg_minify',
          'cache_bootstrap',
          'cache_config',
          'cache_container',
          'cache_data',
          'cache_default',
          'cache_discovery',
          'cache_discovery_migration',
          'cache_dynamic_page_cache',
          'cache_entity',
          'cache_menu',
          'cache_migrate',
          'cache_page',
          'cache_render',
          'cache_rest',
          'cache_toolbar',
          'sessions',
          'watchdog',
          'webprofiler',
        ],
      ];

      // @todo Allow modules to add their own excluded tables.
      $bam->plugins()->add('db_exclude', new DBExcludeFilter(new Config($config)));
    }
  }

}
