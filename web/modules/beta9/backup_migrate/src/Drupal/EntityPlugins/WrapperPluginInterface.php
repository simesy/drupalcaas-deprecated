<?php

namespace Drupal\backup_migrate\Drupal\EntityPlugins;

use Drupal\backup_migrate\Core\Main\BackupMigrateInterface;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;

/**
 * An interface for a plugin which wraps a Backup and Migrate plugin.
 *
 * @package Drupal\backup_migrate\Drupal\EntityPlugins
 */
interface WrapperPluginInterface extends ConfigurableInterface, DependentPluginInterface {

  /**
   * Alter the backup_migrate object to add the source and required services.
   *
   * @param \Drupal\backup_migrate\Core\Main\BackupMigrateInterface $bam
   *   The BackupMigrate object to add plugins and services to.
   * @param string $key
   *   The id of the source to add.
   * @param array $options
   *   The alter options.
   *
   * @see hook_backup_migrate_service_object_alter()
   *
   * @return mixed
   */
  public function alterBackupMigrate(BackupMigrateInterface $bam, $key, array $options = []);

}
