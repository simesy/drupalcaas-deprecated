<?php

namespace Drupal\backup_migrate\Core\Service;

use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 * Interface ArchiveWriterInterface.
 *
 * @package Drupal\backup_migrate\Core\Environment
 */
interface ArchiveReaderInterface {

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

  // Public function listFiles()
  // public function extractFile($from, $to);.

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
