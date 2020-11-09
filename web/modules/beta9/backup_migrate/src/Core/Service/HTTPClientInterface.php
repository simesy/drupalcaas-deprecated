<?php

namespace Drupal\backup_migrate\Core\Service;

use Drupal\backup_migrate\Core\File\ReadableStreamBackupFile;

/**
 * Interface HttpClientInterface.
 *
 * @package Drupal\backup_migrate\Core\Service
 */
interface HttpClientInterface {

  /**
   * Get the body of the given resource.
   *
   * @param $url
   *
   * @return mixed
   */
  public function get($url);

  /**
   * Post the given data (as a string or an array) to the given URL.
   *
   * @param $url
   * @param $data
   *
   * @return mixed
   */
  public function post($url, $data);

  /**
   * Post a file along with other data (as an array).
   *
   * @param $url
   * @param \Drupal\backup_migrate\Core\File\ReadableStreamBackupFile $file
   * @param $data
   *
   * @return mixed
   */
  public function postFile($url, ReadableStreamBackupFile $file, $data);

}
