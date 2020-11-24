<?php

namespace Drupal\Tests\lecapi\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\lecapi\Ia;
use Drupal\Tests\jsonapi\Functional\JsonApiRequestTestTrait;
use Drupal\Tests\lecapi\LecapiTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Class for testing paragraph html.
 */
class LecapiParagraphHtmlTest extends LecapiTestBase {

  use JsonApiRequestTestTrait;

  /**
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The account to use for authentication.
   *
   * @var null|\Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Setup entity for testing.
    $this->account = $this->drupalCreateUser([], $this->randomMachineName(), TRUE);
    $this->entity = $this->setUpTestingEntity();
  }

  /**
   * Tests GETting an individual resource, plus edge cases to ensure good DX.
   */
  public function testGetIndividual() {
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', 'paragraph--markup'), ['entity' => $this->entity->uuid()]);
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $this->drupalLogin($this->account);
    $response = $this->request('GET', $url, $request_options);
    $expected_document = $this->getExpectedDocument();
    // So sanh Code response.
    $this->assertEqual($response->getStatusCode(), 200);
    // So sanh documet respone.
    $actual_document = Json::decode($response->getBody()->__toString());
    $this->assertArraySimilar($expected_document, $actual_document);

  }

  /**
   * Get expected document.
   */
  protected function getExpectedDocument() {
    $self_url = Url::fromUri('base:/jsonapi/paragraph/markup/' . $this->entity->uuid())->setAbsolute()->toString(TRUE)->getGeneratedUrl();
    return [
      'jsonapi' => [
        'version' => '1.0',
        'meta' => [
          'links' => [
            'self' => ['href' => 'http://jsonapi.org/format/1.0/'],
          ],
        ],
      ],
      'data' => [
        'type' => 'paragraph--markup',
        'id' => $this->entity->uuid(),
        'links' => [
          'self' => ['href' => $self_url],
        ],
        'attributes' => [
          'drupal_internal__id' => $this->entity->id(),
          'drupal_internal__revision_id' => $this->entity->getRevisionId(),
          'langcode' => $this->entity->language()->getId(),
          'status' => TRUE,
          'created' => (new \DateTime())->setTimestamp($this->entity->getCreatedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'parent_id' => $this->entity->getParentEntity()->id(),
          'parent_type' => $this->entity->getParentEntity()->getEntityTypeId(),
          'parent_field_name' => 'content',
          'behavior_settings' => [],
          'default_langcode' => TRUE,
          'revision_translation_affected' => TRUE,
          '_markup' => [
            'value' => '<p>This is text markup</p>',
            'format' => 'basic',
            'processed' => "<p>This is text markup</p>",
          ],
        ],
        'relationships' => [
          'paragraph_type' => [
            'data' => [
              'type' => 'paragraphs_type--paragraphs_type',
              'id' => $this->entity->getParagraphType()->uuid(),
            ],
            'links' => [
              'related' => ['href' => $self_url . '/paragraph_type'],
              'self' => ['href' => $self_url . '/relationships/paragraph_type'],
            ],
          ],
          'subtitle' => [
            'data' => [
              'type' => 'paragraph--subtitle',
              'id' => $this->entity->get('subtitle')->first()->entity->uuid(),
              'meta' => [
                'target_revision_id' => $this->entity->get('subtitle')->first()->entity->getRevisionId(),
              ],
            ],
            'links' => [
              'related' => ['href' => $self_url . '/subtitle'],
              'self' => ['href' => $self_url . '/relationships/subtitle'],
            ],
          ],
        ],
      ],
      'links' => [
        'self' => ['href' => $self_url],
      ],
    ];
  }

  /**
   * Assert same document.
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

  /**
   * Setup entity for testing.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Return to Entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUpTestingEntity() {
    $customer_user = $this->getCustomer();
    $site_term = $this->getSiteTerm();
    $this->addUserToSite($customer_user, $site_term);
    $subtitle_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => 'subtitle',
        '_anchor' => 'anchor-text',
        '_heading' => 'Subtitle',
      ]);
    $html_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => 'markup',
        '_markup' => [
          'value' => '<p>This is text markup</p>',
          'format' => 'basic',
        ],
        'subtitle' => $subtitle_paragraph,
      ]);
    /** @var \Drupal\node\Entity\Node $page_node */
    $page_node = $this->entityTypeManager
      ->getStorage('node')
      ->create([
        'type' => 'page',
        'content' => [
          $html_paragraph,
        ],
        'title' => 'Test HTML component',
        'uid' => $this->account->id(),
        Ia::FIELD_SITE => $this->getSiteTerm(),
      ]);
    $page_node->save();
    // Set html paragraph as entity be test.
    $referenced_entities = $page_node->get('content')->referencedEntities();
    return reset($referenced_entities);
  }

}
