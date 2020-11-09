<?php

namespace Drupal\backup_migrate\Core\Destination;

use Drupal\backup_migrate\Core\Config\ConfigurableInterface;
use Drupal\backup_migrate\Core\Exception\DestinationNotWritableException;
use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\File\BackupFile;
use Drupal\backup_migrate\Core\File\BackupFileInterface;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;
use Drupal\backup_migrate\Core\File\ReadableStreamBackupFile;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Destination
 */
class DirectoryDestination extends DestinationBase implements ListableDestinationInterface, ReadableDestinationInterface, ConfigurableInterface, FileProcessorInterface {
  use SidecarMetadataDestinationTrait;

  /**
   * {@inheritdoc}
   */
  public function saveFile(BackupFileReadableInterface $file) {
    $this->saveTheFile($file);
    $this->saveTheFileMetadata($file);
  }

  /**
   * {@inheritdoc}
   */
  public function checkWritable() {
    $this->checkDirectory();
  }

  /**
   * Get a definition for user-configurable settings.
   *
   * @param array $params
   *
   * @return array
   */
  public function configSchema(array $params = []) {
    $schema = [];

    // Init settings.
    if ($params['operation'] == 'initialize') {
      $schema['fields']['directory'] = [
        'type' => 'text',
        'title' => $this->t('Directory Path'),
      ];
    }

    return $schema;
  }

  /**
   * Do the actual file save.
   *
   * This function is called to save the data file AND the metadata sidecar
   * file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $file
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public function saveTheFile(BackupFileReadableInterface $file) {
    // Check if the directory exists.
    $this->checkDirectory();

    copy($file->realpath(), $this->idToPath($file->getFullName()));
    // @todo Use copy/unlink if the temp file and the destination do not share
    // a stream wrapper.
  }

  /**
   * Check that the directory can be used for backup.
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  protected function checkDirectory() {
    $dir = $this->confGet('directory');

    // Check if the directory exists.
    if (!file_exists($dir)) {
      throw new DestinationNotWritableException(
        "The backup file could not be saved to '%dir' because it does not exist.",
        ['%dir' => $dir]
      );
    }

    // Check if the directory is writable.
    if (!is_writable($this->confGet('directory'))) {
      throw new DestinationNotWritableException(
        "The backup file could not be saved to '%dir' because Backup and Migrate does not have write access to that directory.",
        ['%dir' => $dir]
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFile($id) {
    if ($this->fileExists($id)) {
      $out = new BackupFile();
      $out->setMeta('id', $id);
      $out->setFullName($id);
      return $out;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadFileForReading(BackupFileInterface $file) {
    // If this file is already readable, simply return it.
    if ($file instanceof BackupFileReadableInterface) {
      return $file;
    }

    $id = $file->getMeta('id');
    if ($this->fileExists($id)) {
      return new ReadableStreamBackupFile($this->idToPath($id));
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function listFiles() {
    $dir = $this->confGet('directory');
    $out = [];

    // Get the entire list of filenames.
    $files = $this->getAllFileNames();

    foreach ($files as $file) {
      $filepath = $dir . '/' . $file;
      $out[$file] = new ReadableStreamBackupFile($filepath);
    }

    return $out;
  }

  /**
   * {@inheritdoc}
   */
  public function queryFiles(array $filters = [], $sort = 'datestamp', $sort_direction = SORT_DESC, $count = 100, $start = 0) {
    // Get the full list of files.
    $out = $this->listFiles($count + $start);
    foreach ($out as $key => $file) {
      $out[$key] = $this->loadFileMetadata($file);
    }

    // Filter the output.
    if ($filters) {
      $out = array_filter($out, function ($file) use ($filters) {
        foreach ($filters as $key => $value) {
          if ($file->getMeta($key) !== $value) {
            return FALSE;
          }
        }
        return TRUE;
      });
    }

    // Sort the files.
    if ($sort && $sort_direction) {
      uasort($out, function ($a, $b) use ($sort, $sort_direction) {
        if ($sort_direction == SORT_DESC) {
          return $b->getMeta($sort) < $b->getMeta($sort);
        }
        else {
          return $b->getMeta($sort) > $b->getMeta($sort);
        }
      });
    }

    // Slice the return array.
    if ($count || $start) {
      $out = array_slice($out, $start, $count);
    }

    return $out;
  }

  /**
   * @return int
   *   The number of files in the destination.
   */
  public function countFiles() {
    $files = $this->getAllFileNames();
    return count($files);
  }

  /**
   * {@inheritdoc}
   */
  public function fileExists($id) {
    return file_exists($this->idToPath($id));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTheFile($id) {
    if ($file = $this->getFile($id)) {
      if ($file = $this->loadFileForReading($file)) {
        return unlink($file->realpath());
      }
    }
    return FALSE;
  }

  /**
   * Return a file path for the given file id.
   *
   * @param $id
   *
   * @return string
   */
  protected function idToPath($id) {
    return rtrim($this->confGet('directory'), '/') . '/' . $id;
  }

  /**
   * Get the entire file list from this destination.
   *
   * @return array
   */
  protected function getAllFileNames() {
    $files = [];

    // Read the list of files from the directory.
    $dir = $this->confGet('directory');

    /** @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager */
    $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager');
    $scheme = $stream_wrapper_manager->getScheme($dir);

    // Ensure the stream is configured.
    if (!$stream_wrapper_manager->isValidScheme($scheme)) {
      \Drupal::messenger()->addMessage($this->t('Your @scheme stream is not configured.', [
        '@scheme' => $scheme . '://',
      ]), 'warning');
      return $files;
    }

    if ($handle = opendir($dir)) {
      while (FALSE !== ($file = readdir($handle))) {
        $filepath = $dir . '/' . $file;
        // Don't show hidden, unreadable or metadata files.
        if (substr($file, 0, 1) !== '.' && is_readable($filepath) && substr($file, strlen($file) - 5) !== '.info') {
          $files[] = $file;
        }
      }
    }

    return $files;
  }

}
