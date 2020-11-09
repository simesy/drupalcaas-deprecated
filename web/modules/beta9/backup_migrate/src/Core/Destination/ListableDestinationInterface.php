<?php

namespace Drupal\backup_migrate\Core\Destination;

/**
 * Interface ListableDestinationInterface.
 *
 * @package Drupal\backup_migrate\Core\Destination
 */
interface ListableDestinationInterface extends DestinationInterface {

  /**
   * Return a list of files from the destination.
   *
   * This list should be date ordered from newest to oldest.
   *
   * @todo Decide if extended metadata should ALWAYS be loaded here. Is there
   * a use case for getting a list of files WITHOUT metadata?
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileInterface[]
   *   An array of BackupFileInterface objects representing the files with
   *   the file ids as keys. The file ids are usually file names but that
   *   is up to the implementing destination to decide. The returned files
   *   may not be readable. Use loadFileForReading to get a readable file.
   */
  public function listFiles();

  /**
   * Run a basic query with sort on the list of files.
   *
   * @param array $filters
   *   An array of of metadata fields to filter by.
   * @param string $sort
   *   A metadata field to sort by, defaults to 'datestamp'.
   * @param int $sort_direction
   *   The direction to sort by, either SORT_ASC or SORT_DESC.
   * @param int $count
   *   The number of records to obtain.
   * @param int $start
   *   The first item to start the result set from.
   *
   * @return mixed
   */
  public function queryFiles(array $filters = [], $sort = 'datestamp', $sort_direction = SORT_DESC, $count = 100, $start = 0);

  /**
   * @return int The number of files in the destination.
   */
  public function countFiles();

  /**
   * Does the file with the given id (filename) exist in this destination.
   *
   * @param int $id
   *   The id (usually the filename) of the file.
   *
   * @return bool
   *   Whether the file exists in this destination.
   */
  public function fileExists($id);

  /**
   * Delete the specified file.
   */
  public function deleteFile($id);

}
