<?php

namespace Drupal\backup_migrate\Core\File;

/**
 * A service to provision temp files in the correct place for the environment.
 */
interface TempFileAdapterInterface {

  /**
   * Get a temporary file that can be written to.
   *
   * @param string $ext
   *   The file extension to add to the temp file.
   *
   * @return string
   *   The path to the file.
   */
  public function createTempFile($ext = '');

  /**
   * Delete a temporary file.
   *
   * @param string $filename
   *   The path to the file.
   */
  public function deleteTempFile($filename);

  /**
   * Delete all temp files which have been created.
   */
  public function deleteAllTempFiles();

}
