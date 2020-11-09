<?php

namespace Drupal\backup_migrate\Core\File;

/**
 * A service to provision temp files in the correct place for the environment.
 */
interface BackupFileWritableInterface extends BackupFileReadableInterface {

  /**
   * Get the realpath of the file.
   *
   * @return string
   *   The path or stream URI to the file or NULL if the file does not exist.
   */
  public function realpath();

  /**
   * Write a line to the file.
   *
   * @param string $data
   *   A string to write to the file.
   */
  public function write($data);

  /**
   * Open the file, write the given contents and close it.
   *
   * Used for small amounts of data that can fit in memory.
   *
   * @param string $data
   *   The contents to write.
   */
  public function writeAll($data);

  /**
   * Get a metadata value.
   *
   * @param string $key The key for the metadata item.
   *
   * @return mixed
   *   The value of the metadata for this file.
   */
  // Public function getMeta($key);

  /**
   * Set a metadata value.
   *
   * @param string $key
   *   The key for the metadata item.
   * @param mixed $value
   *   The value for the metadata item.
   */
  public function setMeta($key, $value);

  /**
   * Set a metadata value.
   *
   * @param array $values
   *   An array of key-value pairs for the file metadata.
   */
  public function setMetaMultiple(array $values);

  /**
   * Open a file for reading or writing.
   *
   * @param bool $binary
   *   If true open as a binary file.
   */
  public function openForWrite($binary = FALSE);

  /**
   * Close a file when we're done reading/writing.
   */
  public function close();

}
