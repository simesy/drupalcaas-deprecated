<?php

namespace Drupal\backup_migrate\Core\File;

/**
 * A TempFileManager is in charge of creating new temp files.
 *
 * .. for writing and tracking all created files so they can be deleted. It is
 * also in charge of copying metadata from one temp file to another as the file
 * gets sent through the chain of plugins.
 *
 * @package Drupal\backup_migrate\Core\Services
 */
interface TempFileManagerInterface {

  /**
   * Create a brand new temp file with the given extension (if specified).
   *
   * The new file should be writable.
   *
   * @param string $ext
   *   The file extension for this file (optional)
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileWritableInterface
   */
  public function create($ext = '');

  /**
   * Return a new file based on the passed in file with the given file ext.
   *
   * This should maintain the metadata of the file passed in with the new file
   * extension added after the old one.
   *
   * For example: xxx.mysql would become xxx.mysql.gz.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   *   The file to add the extension to.
   * @param $ext
   *   The new file extension.
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileWritableInterface
   *   A new writable backup file with the new extension and all of the metadata
   *   from the previous file.
   */
  public function pushExt(BackupFileInterface $file, $ext);

  /**
   * Return a new file based on the one passed.
   *
   * Has the last part of the file extension removed.
   *
   * For example: xxx.mysql.gz would become xxx.mysql.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileWritableInterface
   *   A new writable backup file with the last extension removed and
   *   all of the metadata from the previous file.
   */
  public function popExt(BackupFileInterface $file);

}
