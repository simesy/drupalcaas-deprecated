<?php

namespace Drupal\backup_migrate\Plugin\BackupMigrateSource;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Filter\FileExcludeFilter;
use Drupal\backup_migrate\Core\Main\BackupMigrateInterface;
use Drupal\backup_migrate\Drupal\EntityPlugins\SourcePluginBase;

/**
 * Defines an default database source plugin.
 *
 * @BackupMigrateSourcePlugin(
 *   id = "DrupalFiles",
 *   title = @Translation("Public Files"),
 *   description = @Translation("Back up the Drupal public files."),
 *   wrapped_class = "\Drupal\backup_migrate\Core\Source\FileDirectorySource",
 *   locked = true
 * )
 */
class DrupalFilesSourcePlugin extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function alterBackupMigrate(BackupMigrateInterface $bam, $key, $options = []) {
    $source = $this->getObject();
    $bam->sources()->add($key, $source);

    $config = [
      'exclude_filepaths' => [],
      'source' => $source,
    ];

    switch ($this->getConfig()->get('directory')) {
      case 'public://':
        $config['exclude_filepaths'] = [
          'js',
          'css',
          'php',
          'styles',
          'config_*',
          '.htaccess',
        ];
        break;

      case 'private://':
        $config['exclude_filepaths'] = [
          'backup_migrate',
        ];
        break;
    }

    // @todo Allow modules to add their own excluded defaults.
    $bam->plugins()->add($key . '_exclude', new FileExcludeFilter(new Config($config)));
  }

}
