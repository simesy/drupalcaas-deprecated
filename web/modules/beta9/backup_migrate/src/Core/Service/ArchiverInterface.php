<?php

namespace Drupal\backup_migrate\Core\Service;

use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 * Interface ArchiveWriterInterface.
 *
 * @package Drupal\backup_migrate\Core\Environment
 */
interface ArchiverInterface {

  /**
   * Get the file extension for this archiver.
   *
   * For a tarball writer this would be 'tar'. For a Zip file writer this would
   * be 'zip'.
   *
   * @return string
   */
  public function getFileExt();

  /**
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $out
   */
  public function setArchive(BackupFileReadableInterface $out);

  /**
   * Extract all files to the given directory.
   *
   * @param $directory
   *
   * @return mixed
   */
  public function extractTo($directory);

  /**
   * @param string $real_path
   *   The real path to the file. Can be a stream URI.
   * @param string $base_dir
   *   The base directory of the path to be removed when the file is added.
   *
   * @return
   */
  public function addFile($real_path, $base_dir = '');

  /**
   * This will be called when all files have been added.
   *
   * It gives the implementation a chance to clean up and commit the changes if
   * needed.
   *
   * @return mixed
   */
  public function closeArchive();

}
