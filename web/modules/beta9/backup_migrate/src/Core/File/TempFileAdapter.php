<?php

namespace Drupal\backup_migrate\Core\File;

use Drupal\backup_migrate\Core\Exception\BackupMigrateException;

/**
 * A very basic temp file manager.
 *
 * Assumes read/write access to a local temp directory.
 */
class TempFileAdapter implements TempFileAdapterInterface {

  /**
   * The path to the temp directory.
   *
   * @var string
   */
  protected $dir;

  /**
   * A prefix to add to all temp files.
   *
   * @var string
   */
  protected $prefix;

  /**
   * The list of files created by this manager.
   *
   * @var array
   */
  protected $tempfiles;

  /**
   * Construct a manager.
   *
   * @param string $dir
   *   A file path or stream URL for the temp directory.
   * @param string $prefix
   *   A string prefix to add to each created file.
   */
  public function __construct($dir, $prefix = 'bam') {
    // Add a trailing slash if needed.
    if (substr($dir, -1) !== '/') {
      $dir .= '/';
    }
    $this->dir = $dir;
    $this->prefix = $prefix;
    $this->tempfiles = [];
    // @todo check that temp direcory is writeable or throw an exception.
  }

  /**
   * Destruct the manager.
   *
   * Delete all the temporary files when this manager is destroyed.
   */
  public function __destruct() {
    $this->deleteAllTempFiles();
  }

  /**
   * {@inheritdoc}
   */
  public function createTempFile($ext = '') {
    // Add a dot to the file extension.
    $ext = $ext ? '.' . $ext : '';

    // Find an unused random file name.
    $try = 5;
    do {
      $out = $this->dir . $this->prefix . mt_rand() . $ext;
      $fp = @fopen($out, 'x');
    } while (!$fp && $try-- > 0);
    if ($fp) {
      fclose($fp);
    }
    else {
      throw new \Exception('Could not create a temporary file to write to.');
    }

    $this->tempfiles[] = $out;
    return $out;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTempFile($filename) {
    // Only delete files that were created by this manager.
    if (in_array($filename, $this->tempfiles)) {
      if (file_exists($filename)) {
        if (is_writable($filename)) {
          unlink($filename);
        }
        else {
          throw new BackupMigrateException('Could not delete the temp file: %file because it is not writable', ['%file' => $filename]);
        }
      }
      // Remove the item from the list.
      $this->tempfiles = array_diff($this->tempfiles, [$filename]);
      return;
    }
    throw new BackupMigrateException('Attempting to delete a temp file not managed by this codebase: %file', ['%file' => $filename]);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAllTempFiles() {
    foreach ($this->tempfiles as $file) {
      $this->deleteTempFile($file);
    }
  }

}
