<?php

namespace Drupal\backup_migrate\Core\Service;

use Drupal\backup_migrate\Core\Exception\HttpClientException;
use Drupal\backup_migrate\Core\File\ReadableStreamBackupFile;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Service
 */
class PhpCurlHttpClient implements HttpClientInterface {

  /**
   * Get the body of the given resource.
   *
   * @param $url
   *
   * @return mixed
   */
  public function get($url) {
    // @todo Implement if needed.
  }

  /**
   * Post the given data (as a string or an array) to the given URL.
   *
   * @param $url
   * @param $data
   *
   * @return mixed
   */
  public function post($url, $data) {
    $ch = $this->getCurlResource($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    return $this->curlExec($ch);
  }

  /**
   * Post a file along with other data (as an array).
   *
   * @param $url
   * @param \Drupal\backup_migrate\Core\File\ReadableStreamBackupFile $file
   * @param $data
   *
   * @return mixed
   */
  public function postFile($url, ReadableStreamBackupFile $file, $data) {
    $data['file'] = new \CURLFile($file->realpath());
    $data['file']->setPostFilename($file->getFullName());
    return $this->post($url, $data);
  }

  /**
   * Get the CURL Resource with default options.
   *
   * @param $url
   *
   * @return resource
   */
  protected function getCurlResource($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    return $ch;
  }

  /**
   * Perform the http action and return the body or throw an exception.
   *
   * @param $ch
   *
   * @return mixed
   *
   * @throws \Drupal\backup_migrate\Core\Exception\HttpClientException
   */
  protected function curlExec($ch) {
    $body = curl_exec($ch);
    if ($msg = curl_error($ch)) {
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if (!$code) {
        $info['code'] = curl_errno($ch);
      }
      throw new HttpClientException($msg, [], $code);
    }
    return $body;
  }

}
