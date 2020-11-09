<?php

namespace Drupal\backup_migrate\Drupal\Filter;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Filter\FileExcludeFilter;

/**
 * A file exclusion filter that includes Drupal's cache directories by default.
 *
 * @package Drupal\backup_migrate\Drupal\Filter
 */
class DrupalPublicFileExcludeFilter extends FileExcludeFilter {

  /**
   * Get the default values for the plugin.
   *
   * @return \Drupal\backup_migrate\Core\Config\Config
   */
  public function configDefaults() {
    $config = [
      'exclude_filepaths' => [
        'js',
        'css',
        'php',
        'styles',
        'config_*',
        '.htaccess',
      ],
    ];

    // @todo Allow modules to add their own excluded defaults.
    return new Config($config);
  }

}
