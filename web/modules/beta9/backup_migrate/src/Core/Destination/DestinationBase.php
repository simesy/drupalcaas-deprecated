<?php

namespace Drupal\backup_migrate\Core\Destination;

use Drupal\backup_migrate\Core\Exception\DestinationNotWritableException;
use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\File\BackupFileInterface;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Destination
 */
abstract class DestinationBase extends PluginBase implements ReadableDestinationInterface, WritableDestinationInterface {

  /**
   * Get a list of supported operations and their weight.
   *
   * @return array
   */
  public function supportedOps() {
    return [];
  }

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
  public function loadFileMetadata(BackupFileInterface $file) {
    // If this file is already loaded, simply return it.
    // @todo Fix this inappropriate use of file metadata.
    if (!$file->getMeta('metadata_loaded')) {
      $metadata = $this->loadFileMetadataArray($file);
      $file->setMetaMultiple($metadata);
      $file->setMeta('metadata_loaded', TRUE);
    }
    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteFile($id) {
    return $this->deleteTheFile($id);
  }

  /**
   * {@inheritdoc}
   */
  public function isRemote() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function checkWritable() {
    throw new DestinationNotWritableException('The specified destination cannot be written to.');
  }

  /**
   * Do the actual delete for a file.
   *
   * @param string $id
   *   The id of the file to delete.
   */
  abstract protected function deleteTheFile($id);

  /**
   * Do the actual file save.
   *
   * Should take care of the actual creation of a file in the destination
   * without regard for metadata.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $file
   */
  abstract protected function saveTheFile(BackupFileReadableInterface $file);

  /**
   * Do the metadata save.
   *
   * This function is called to save the data file AND the metadata sidecar
   * file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   */
  abstract protected function saveTheFileMetadata(BackupFileInterface $file);

  /**
   * Load the actual metadata for the file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileInterface $file
   */
  abstract protected function loadFileMetadataArray(BackupFileInterface $file);

}
