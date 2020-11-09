<?php

namespace Drupal\backup_migrate\Core\Destination;

use Drupal\backup_migrate\Core\File\BackupFileInterface;

/**
 * Interface ReadableDestinationInterface.
 *
 * @package Drupal\backup_migrate\Core\Destination
 */
interface ReadableDestinationInterface extends DestinationInterface {

  /**
   * Get a file object representing the file with the given ID from the dest.
   *
   * This file item will not necessarily be readable nor will it have extended
   * metadata loaded. Use loadForReading and loadFileMetadata to get those.
   *
   * @todo Decide if extended metadata should ALWAYS be loaded here.
   *
   * @param string $id
   *   The unique identifier for the file. Usually the filename.
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileInterface
   *   The file if it exists or NULL if it doesn't
   */
  public function getFile($id);

  /**
   * Load the metadata for the given file however it may be stored.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileInterface
   */
  public function loadFileMetadata(BackupFileInterface $file);

  /**
   * Load the file with the given ID from the destination.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileReadableInterface
   *   The file if it exists or NULL if it doesn't.
   */
  public function loadFileForReading(BackupFileInterface $file);

  /**
   * Does the file with the given id (filename) exist in this destination.
   *
   * @param string $id
   *   The id (usually the filename) of the file.
   *
   * @return bool
   *   Whether the file exists in this destination.
   */
  public function fileExists($id);

}
