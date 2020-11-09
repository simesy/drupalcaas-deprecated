<?php

namespace Drupal\backup_migrate\Plugin\BackupMigrateSource;

use Drupal\backup_migrate\Drupal\EntityPlugins\SourcePluginBase;

/**
 * Defines an mysql source plugin.
 *
 * @BackupMigrateSourcePlugin(
 *   id = "FileDirectory",
 *   title = @Translation("File Directory"),
 *   description = @Translation("Back up a server file directory."),
 *   wrapped_class = "\Drupal\backup_migrate\Core\Source\FileDirectorySource"
 * )
 */
class FileDirectorySourcePlugin extends SourcePluginBase {}
