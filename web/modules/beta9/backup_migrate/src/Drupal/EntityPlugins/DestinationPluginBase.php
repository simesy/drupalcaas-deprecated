<?php

namespace Drupal\backup_migrate\Drupal\EntityPlugins;

use Drupal\backup_migrate\Core\Main\BackupMigrateInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\EntityPlugins
 */
abstract class DestinationPluginBase extends WrapperPluginBase implements DestinationPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function alterBackupMigrate(BackupMigrateInterface $bam, $key, $options = []) {
    $bam->destinations()->add($key, $this->getObject());
  }

}
