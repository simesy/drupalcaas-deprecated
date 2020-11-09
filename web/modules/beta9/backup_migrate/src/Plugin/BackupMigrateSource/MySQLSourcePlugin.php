<?php

namespace Drupal\backup_migrate\Plugin\BackupMigrateSource;

use Drupal\backup_migrate\Drupal\EntityPlugins\SourcePluginBase;

/**
 * Defines an mysql source plugin.
 *
 * @BackupMigrateSourcePlugin(
 *   id = "MySQL",
 *   title = @Translation("MySQL Database"),
 *   description = @Translation("Back up a MySQL compatible database."),
 *   wrapped_class = "\Drupal\backup_migrate\Core\Source\MySQLiSource"
 * )
 */
class MySQLSourcePlugin extends SourcePluginBase {}
