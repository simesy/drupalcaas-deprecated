<?php

namespace Drupal\backup_migrate\Drupal\EntityPlugins;

use Drupal\backup_migrate\Core\Main\BackupMigrateInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\EntityPlugins
 */
abstract class SourcePluginBase extends WrapperPluginBase implements SourcePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function alterBackupMigrate(BackupMigrateInterface $bam, $key, $options = []) {
    $bam->sources()->add($key, $this->getObject());
  }

}
