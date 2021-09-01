<?php

namespace Drupal\Tests\caas\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Random;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\Tests\caas\CaasTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test file resource jsonapi.
 */
class CaasResourceFileTest extends CaasTestBase {

  /**
   * Test uri.absolute of file entity.
   */
  public function testAbsoluteFileUri() {
    // Create admin account and login.
    $account_authenticate = $this->drupalCreateUser([], $this->randomMachineName(), TRUE);
    $this->drupalLogin($account_authenticate);
    // Create example file entity.
    $random = new Random();
    $dirname = 'public://';
    \Drupal::service('file_system')->prepareDirectory($dirname, FileSystemInterface::CREATE_DIRECTORY);
    // Generate a file entity.
    $destination = $dirname . '/file-test.txt';
    $data = $random->paragraphs(3);
    $entity = file_save_data($data, $destination, FileSystemInterface::EXISTS_REPLACE);
    $this->markEntityForCleanup($entity);
    // Perform request.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', $entity->getEntityTypeId() . '--' . $entity->bundle()), ['entity' => $entity->uuid()]);
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $response = $this->request('GET', $url, $request_options);
    // Assert response code.
    $this->assertEquals(200, $response->getStatusCode());
    // Assert response body.
    $actual_document = Json::decode($response->getBody()->__toString());
    $this->assertSame(file_create_url($destination), $actual_document['data']['attributes']['uri']['absolute']);
  }

}
