<?php

namespace Drupal\backup_migrate\Core\File;

/**
 * Uses a readable PHP stream such as a local file.
 *
 * @package Drupal\backup_migrate\Core\File
 */
class ReadableStreamBackupFile extends BackupFile implements BackupFileReadableInterface {

  /**
   * A file handle if it is open.
   *
   * @var resource
   */
  protected $handle;

  /**
   * Constructor.
   *
   * @param string $filepath
   *   The path to a file (which must already exist). Can be a stream URI.
   *
   * @throws \Exception
   */
  public function __construct($filepath) {
    // Check that the file exists and is readable.
    if (!file_exists($filepath)) {
      throw new \Exception("The file '$filepath' does not exists");
    }
    if (!is_readable($filepath)) {
      throw new \Exception("The file '$filepath' cannot be read");
    }

    $this->path = $filepath;

    // Get the basename and extensions.
    $this->setFullName(basename($filepath));

    // Get the basic file stats since this is probably a read-only file option
    // and these won't change.
    $this->loadFileStats();
  }

  /**
   * Destructor.
   */
  public function __destruct() {
    // Close the handle if we've opened it.
    $this->close();
  }

  /**
   * Get the realpath of the file.
   *
   * @return string
   *   The path or stream URI to the file or NULL if the file does not exist.
   */
  public function realpath() {
    if (file_exists($this->path)) {
      return $this->path;
    }
    return NULL;
  }

  /**
   * Open a file for reading or writing.
   *
   * @param bool $binary
   *   If true open as a binary file.
   *
   * @return resource
   *
   * @throws \Exception
   */
  public function openForRead($binary = FALSE) {
    if (!$this->isOpen()) {
      $path = $this->realpath();

      if (!is_readable($path)) {
        // @todo Throw better exception
        throw new \Exception('Cannot read file.');
      }

      // Open the file.
      $mode = "r" . ($binary ? "b" : "");
      $this->handle = fopen($path, $mode);
      if (!$this->handle) {
        throw new \Exception('Cannot open file.');
      }
    }
    // If the file is already open, rewind it.
    $this->rewind();
    return $this->handle;
  }

  /**
   * Close a file when we're done reading/writing.
   */
  public function close() {
    if ($this->isOpen()) {
      fclose($this->handle);
      $this->handle = NULL;
    }
  }

  /**
   * Is this file open for reading/writing.
   *
   * @return bool
   *   True if the file is open, false if not.
   */
  public function isOpen() {
    return !empty($this->handle) && get_resource_type($this->handle) == 'stream';
  }

  /**
   * Read a line from the file.
   *
   * @param int $size
   *   The number of bites to read or 0 to read the whole file.
   *
   * @return string
   *   The data read from the file or NULL if the file can't be read or is at
   *   the end of the file.
   */
  public function readBytes($size = 1024, $binary = FALSE) {
    if (!$this->isOpen()) {
      $this->openForRead($binary);
    }
    if ($this->handle && !feof($this->handle)) {
      return fread($this->handle, $size);
    }
    return NULL;
  }

  /**
   * Read a single line from the file.
   *
   * @return string
   *   The data read from the file or NULL if the file can't be read or is at
   *   the end of the file.
   */
  public function readLine() {
    if (!$this->isOpen()) {
      $this->openForRead();
    }
    return fgets($this->handle);
  }

  /**
   * Read a line from the file.
   *
   * @return string
   *   The data read from the file or NULL if the file can't be read.
   */
  public function readAll() {
    if (!$this->isOpen()) {
      $this->openForRead();
    }
    $this->rewind();
    return stream_get_contents($this->handle);
  }

  /**
   * Move the file pointer forward a given number of bytes.
   *
   * @param int $bytes
   *
   * @return int
   *   The number of bytes moved or -1 if the operation failed.
   */
  public function seekBytes($bytes) {
    if ($this->isOpen()) {
      return fseek($this->handle, $bytes);
    }
    return -1;
  }

  /**
   * Rewind the file handle to the start of the file.
   */
  public function rewind() {
    if ($this->isOpen()) {
      rewind($this->handle);
    }
  }

  /**
   * Get info about the file and load them as metadata.
   */
  protected function loadFileStats() {
    clearstatcache();
    $this->setMeta('filesize', filesize($this->realpath()));
    $this->setMeta('datestamp', filectime($this->realpath()));
  }

}
