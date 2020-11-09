<?php

namespace Drupal\backup_migrate\Core\Destination;

use Drupal\backup_migrate\Core\Config\ConfigurableInterface;
use Drupal\backup_migrate\Core\Exception\DestinationNotWritableException;
use Drupal\backup_migrate\Core\File\BackupFileInterface;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;
use Drupal\backup_migrate\Core\Plugin\PluginBase;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Destination
 */
class StreamDestination extends PluginBase implements WritableDestinationInterface, ConfigurableInterface {

  /**
   * {@inheritdoc}
   */
  public function saveFile(BackupFileReadableInterface $file) {
    $stream_uri = $this->confGet('streamuri');
    if ($fp_out = fopen($stream_uri, 'w')) {
      $file->openForRead();
      while ($data = $file->readBytes(1024 * 512)) {
        fwrite($fp_out, $data);
      }
      fclose($fp_out);
      $file->close();
    }
    else {
      throw new \Exception("Cannot open the file $stream_uri for writing");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkWritable() {
    $stream_uri = $this->confGet('streamuri');

    // The stream must exist.
    if (!file_exists($stream_uri)) {
      throw new DestinationNotWritableException('The file stream !uri does not exist.', ['%uri' => $stream_uri]);
    }

    // The stream must be writable.
    if (!file_exists($stream_uri)) {
      throw new DestinationNotWritableException('The file stream !uri cannot be written to.', ['%uri' => $stream_uri]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFile($id) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadFileMetadata(BackupFileInterface $file) {
    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function loadFileForReading(BackupFileInterface $file) {
    return $file;
  }

}
