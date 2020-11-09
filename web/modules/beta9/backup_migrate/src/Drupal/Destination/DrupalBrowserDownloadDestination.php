<?php

namespace Drupal\backup_migrate\Drupal\Destination;

use Drupal\backup_migrate\Core\Destination\BrowserDownloadDestination;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\Destination
 */
class DrupalBrowserDownloadDestination extends BrowserDownloadDestination {

  /**
   * {@inheritdoc}
   */
  public function saveFile(BackupFileReadableInterface $file) {
    // @todo Replace the header/print calls with a Symfony response (if that
    // allows streaming).
    // Need to find some way to return new BinaryFileResponse($uri, 200
    // $headers); all the way out to the output of the caller.
    // Probably need to provide the response as a service in the environment.
    parent::saveFile($file);

    // @todo Get rid of this ugliness.
    exit();
  }

}
