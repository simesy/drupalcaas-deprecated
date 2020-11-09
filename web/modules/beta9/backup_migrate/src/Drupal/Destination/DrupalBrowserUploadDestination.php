<?php

namespace Drupal\backup_migrate\Drupal\Destination;

use Drupal\backup_migrate\Core\Destination\ReadableDestinationInterface;
use Drupal\backup_migrate\Core\File\BackupFileInterface;
use Drupal\backup_migrate\Core\File\ReadableStreamBackupFile;
use Drupal\backup_migrate\Core\Plugin\PluginBase;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Destination
 */
class DrupalBrowserUploadDestination extends PluginBase implements ReadableDestinationInterface {

  /**
   * {@inheritdoc}
   */
  public function getFile($id) {
    $file_upload = \Drupal::request()->files->get("files", NULL, TRUE)[$id];
    // Make sure there's an upload to process.
    if (!empty($file_upload)) {
      $out = new ReadableStreamBackupFile($file_upload->getRealPath());
      $out->setFullName($file_upload->getClientOriginalName());
      return $out;
    }
  }

  /**
   * Load the metadata for the given file however it may be stored.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileInterface
   */
  public function loadFileMetadata(BackupFileInterface $file) {
    return $file;
  }

  /**
   * Load the file with the given ID from the destination.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileReadableInterface The file if it exists or NULL if it doesn't
   */
  public function loadFileForReading(BackupFileInterface $file) {
    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function fileExists($id) {
    return (boolean) \Drupal::request()->files->has("files[$id]");
  }

}
