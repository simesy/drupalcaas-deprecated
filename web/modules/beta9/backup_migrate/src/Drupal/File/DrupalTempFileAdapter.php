<?php

namespace Drupal\backup_migrate\Drupal\File;

use Drupal\backup_migrate\Core\File\TempFileAdapter;
use Drupal\backup_migrate\Core\File\TempFileAdapterInterface;
use Drupal\Core\File\FileSystem;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\File
 */
class DrupalTempFileAdapter extends TempFileAdapter implements TempFileAdapterInterface {

  /**
   * The Drupal file system for provisioning temp files.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $filesystem;

  /**
   * Construct a manager.
   *
   * @param \Drupal\Core\File\FileSystem $filesystem
   *   A file path or stream URL for the temp directory.
   * @param string $dir
   *   The directory to save to.
   * @param string $prefix
   *   A string prefix to add to each created file.
   */
  public function __construct(FileSystem $filesystem, $dir = 'temporary://', $prefix = 'bam') {
    // Set the prefix and initialize the temp file tracking.
    parent::__construct($dir, $prefix);

    $this->filesystem = $filesystem;
  }

  /**
   * {@inheritdoc}
   */
  public function createTempFile($ext = '') {
    // Add a dot to the file extension.
    $ext = $ext ? '.' . $ext : '';

    $file = $this->filesystem->tempnam($this->dir, $this->prefix);
    if (!$file) {
      throw new \Exception('Could not create a temporary file to write to.');
    }

    $this->tempfiles[] = $file;
    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTempFile($filename) {
    // Only delete files that were created by this manager.
    if (in_array($filename, $this->tempfiles)) {
      if (file_exists($filename)) {
        if (!$this->filesystem->unlink($filename)) {
          throw new \Exception('Could not delete a temporary file.');
        }
      }
    }
  }

}
