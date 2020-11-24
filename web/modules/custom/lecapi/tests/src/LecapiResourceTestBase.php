<?php

namespace Drupal\Tests\lecapi;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Tests\jsonapi\Functional\JsonApiRequestTestTrait;
use GuzzleHttp\RequestOptions;

/**
 * A useful base class for resource json:api tests.
 */
abstract class LecapiResourceTestBase extends LecapiTestBase {

  use JsonApiRequestTestTrait;

  /**
   * Entity being test.
   *
   * @var null|\Drupal\Core\Entity\EntityInterface
   */
  protected $entity = NULL;

  /**
   * The account to use for authentication.
   *
   * @var null|\Drupal\Core\Session\AccountInterface
   */
  protected $account = NULL;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Setup account to perform test.
    $this->account = $this->drupalCreateUser([], $this->randomMachineName(), TRUE);
    // Setup entity for testing.
    $this->entity = $this->setUpTestingEntity();
    // Mark this entity need to be deleted after test.
    $this->markEntityForCleanup($this->entity);
  }

  /**
   * Tests GETting an individual resource.
   */
  public function testGetIndividual() {
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', $this->entity->getEntityTypeId() . '--' . $this->entity->bundle()), ['entity' => $this->entity->uuid()]);
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $this->drupalLogin($this->account);
    $response = $this->request('GET', $url, $request_options);
    $expected_document = $this->getExpectedResponse();
    // Assert response code.
    $this->assertEqual($response->getStatusCode(), 200);
    // Assert response body.
    $actual_document = Json::decode($response->getBody()->__toString());
    $this->assertArraySimilar($expected_document, $actual_document);
  }

  /**
   * Get expected resource response.
   */
  abstract protected function getExpectedResponse();

  /**
   * Setup entity for testing.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Return to Entity.
   */
  abstract protected function setUpTestingEntity();

  /**
   * Assert array similar dont care about key position.
   *
   * @param array $expected
   *   Expected document.
   * @param array $array
   *   Actual document.
   */
  protected function assertArraySimilar(array $expected, array $array) {
    $this->assertEquals([], array_diff_key($array, $expected));
    foreach ($expected as $key => $value) {
      if (is_array($value)) {
        $this->assertArraySimilar($value, $array[$key]);
      }
      else {
        $this->assertContains($value, $array);
      }
    }
  }

}
