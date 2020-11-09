<?php

namespace Drupal\backup_migrate\Core\File;

use Drupal\backup_migrate\Core\Exception\BackupMigrateException;

/**
 * A file object which represents an existing PHP stream with read/write.
 *
 * @package Drupal\backup_migrate\Core\File
 */
class WritableStreamBackupFile extends ReadableStreamBackupFile implements BackupFileReadableInterface, BackupFileWritableInterface {

  /**
   * Dirty bit - has the file been written to since it was opened?
   *
   * @var bool
   */
  protected $dirty = FALSE;

  /**
   * Open a file for reading or writing.
   *
   * @param bool $binary
   *   Is the file binary.
   *
   * @throws \Exception
   */
  public function openForWrite($binary = FALSE) {
    if (!$this->isOpen()) {
      $path = $this->realpath();

      // Check if the file can be read/written.
      if ((file_exists($path) && !is_writable($path)) || (!file_exists($path) && !is_writable(dirname($path)))) {
        // @todo Throw better exception
        throw new BackupMigrateException('Cannot write to file: %path', ['%path' => $path]);
      }

      // Open the file.
      $mode = "w" . ($binary ? "b" : "");
      $this->handle = fopen($path, $mode);
      if (!$this->handle) {
        throw new BackupMigrateException('Cannot open file: %path', ['%path' => $path]);
      }
    }
  }

  /**
   * Write a line to the file.
   *
   * @param string $data
   *   A string to write to the file.
   *
   * @throws \Exception
   */
  public function write($data) {
    if (!$this->isOpen()) {
      $this->openForWrite();
    }

    if ($this->handle) {
      if (fwrite($this->handle, $data) === FALSE) {
        throw new \Exception('Cannot write to file: ' . $this->realpath());
      }
      else {
        $this->dirty = TRUE;
      }
    }
    else {
      throw new \Exception('File not open for writing.');
    }
  }

  /**
   * Update the file time and size when the file is closed.
   */
  public function close() {
    parent::close();

    // If the file has been modified, update the stats from disk.
    if ($this->dirty) {
      $this->loadFileStats();
      $this->dirty = FALSE;
    }
  }

  /**
   * Open the file, writes the given contents and closes it.
   *
   * Used for small amounts of data that can fit in memory.
   *
   * @param $data
   */
  public function writeAll($data) {
    $this->openForWrite();
    $this->write($data);
    $this->close();
  }

}
