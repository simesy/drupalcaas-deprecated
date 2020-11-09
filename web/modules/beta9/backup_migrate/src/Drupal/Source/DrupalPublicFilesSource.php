<?php

namespace Drupal\backup_migrate\Drupal\Source;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Source\FileDirectorySource;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\Source
 */
class DrupalPublicFilesSource extends FileDirectorySource {

  /**
   * Get the default values for the plugin.
   *
   * @return \Drupal\backup_migrate\Core\Config\Config
   */
  public function configDefaults() {
    $config = [
      'directory' => 'public://',
    ];

    return new Config($config);
  }

}
