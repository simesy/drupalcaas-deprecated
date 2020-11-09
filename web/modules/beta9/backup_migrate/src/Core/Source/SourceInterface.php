<?php

namespace Drupal\backup_migrate\Core\Source;

use Drupal\backup_migrate\Core\Plugin\PluginInterface;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 * Provides an interface defining a backup source.
 */
interface SourceInterface extends PluginInterface {

  /**
   * Export this source to the given temp file.
   *
   * This should be the main back up function for this source.
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileReadableInterface
   *   A backup file with the contents of the source dumped to it..
   */
  public function exportToFile();

  /**
   * Import to this source from the given backup file.
   *
   * This is the main restore function for this source.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $file
   *   The file to read the backup from. It will not be opened for reading.
   */
  public function importFromFile(BackupFileReadableInterface $file);

}
