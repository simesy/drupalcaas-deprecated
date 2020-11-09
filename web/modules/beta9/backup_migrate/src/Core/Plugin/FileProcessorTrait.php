<?php

namespace Drupal\backup_migrate\Core\Plugin;

use Drupal\backup_migrate\Core\File\TempFileManagerInterface;

/**
 * Implement the injection functionality of a file processor.
 *
 * @package Drupal\backup_migrate\Core\Plugin
 */
trait FileProcessorTrait {

  /**
   * @var \Drupal\backup_migrate\Core\File\TempFileManagerInterface
   */
  protected $tempfilemanager;

  /**
   * Inject the temp file manager.
   *
   * @param \Drupal\backup_migrate\Core\File\TempFileManagerInterface $tempfilemanager
   *
   * @return mixed
   */
  public function setTempFileManager(TempFileManagerInterface $tempfilemanager) {
    $this->tempfilemanager = $tempfilemanager;
  }

  /**
   * Get the temp file manager.
   *
   * @return \Drupal\backup_migrate\Core\File\TempFileManagerInterface
   */
  public function getTempFileManager() {
    return $this->tempfilemanager;
  }

  /**
   * Provide the file mime for the given file extension if known.
   *
   * @param string $filemime
   *   The best guess so far for the file's mime type.
   * @param array $params
   *   A list of parameters where
   *    'ext' is the file extension we are testing.
   *
   * @return string
   *   The mime type of the file (or the passed in mime type if unknown)
   */
  public function alterMime($filemime, array $params) {
    // Check all of the provided file types for the given extension.
    if (method_exists($this, 'getFileTypes')) {
      $file_types = $this->getFileTypes();
      foreach ($file_types as $info) {
        if (isset($info['extension']) && $info['extension'] == $params['ext'] && isset($info['filemime'])) {
          return $info['filemime'];
        }
      }
    }
    return $filemime;
  }

}
