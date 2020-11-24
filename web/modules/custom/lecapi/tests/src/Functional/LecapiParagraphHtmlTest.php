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
    $this->assertSameDocument($expected_document, $actual_document);

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
              'id' => 'dc9fbc7c-5e38-4cd3-bd86-1f5f8516c183',
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
   * @param array $expected_document
   *   Expected document.
   * @param array $actual_document
   *   Actual document.
   */
  protected function assertSameDocument(array $expected_document, array $actual_document) {
    $expected_keys = array_keys($expected_document);
    $actual_keys = array_keys($actual_document);
    $missing_member_names = array_diff($expected_keys, $actual_keys);
    $extra_member_names = array_diff($actual_keys, $expected_keys);
    if (!empty($missing_member_names) || !empty($extra_member_names)) {
      $message_format = "The document members did not match the expected values. Missing: [ %s ]. Unexpected: [ %s ]";
      $message = sprintf($message_format, implode(', ', $missing_member_names), implode(', ', $extra_member_names));
      $this->assertSame($expected_document, $actual_document, $message);
    }
    foreach ($expected_document as $member_name => $expected_member) {
      $actual_member = $actual_document[$member_name];
      $this->assertSame($expected_member, $actual_member, "The '$member_name' member was not as expected.");
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
