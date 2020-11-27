<?php

namespace Drupal\Tests\lecapi\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\lecapi\Ia;
use Drupal\Tests\lecapi\LecapiTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test case for CTA paragraph.
 */
class LecapiResourceParagraphCalloutTest extends LecapiTestBase {

  /**
   * Tests GETting an individual resource.
   */
  public function testGetIndividual() {
    $account_authenticate = $this->drupalCreateUser([], $this->randomMachineName(), TRUE);
    $this->drupalLogin($account_authenticate);
    // Setup entity for testing then mark it for cleanup.
    $entity = $this->setUpTestingEntity();
    // Build and perform request.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', $entity->getEntityTypeId() . '--' . $entity->bundle()), ['entity' => $entity->uuid()]);
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options[RequestOptions::QUERY]['include'] = implode(',', [Ia::FIELD_MEDIA]);
    $response = $this->request('GET', $url, $request_options);
    // Assert response code.
    $this->assertEquals(200, $response->getStatusCode());
    // Assert response body.
    $actual_document = Json::decode($response->getBody()->__toString());
    $this->assertSame('Callout Heading', $actual_document['data']['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('https://example.com', $actual_document['data']['attributes'][Ia::FIELD_LINK]['uri']);
    $this->assertSame('<p>Test markup</p>', $actual_document['data']['attributes'][Ia::FIELD_MARKUP]['value']);
  }

  /**
   * Setup entity for testing.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Return entity resource for testing.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUpTestingEntity() {
    $customer_user = $this->getCustomer();
    $site_term = $this->getSiteTerm();
    $this->addUserToSite($customer_user, $site_term);
    $media_image = $this->createMedia(['bundle' => 'image']);
    $media_image->media_image->generateSampleItems();
    $media_image->save();
    $callout_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => Ia::PG_CTA,
        Ia::FIELD_HEADING => ['Callout Heading'],
        Ia::FIELD_LINK => ['uri' => 'https://example.com'],
        Ia::FIELD_MARKUP => ['value' => '<p>Test markup</p>', 'format' => 'basic'],
        Ia::FIELD_MEDIA => ['target_id' => $media_image->id()],
      ]);
    /** @var \Drupal\node\Entity\Node $page_node */
    $page_node = $this->drupalCreateNode([
      'type' => 'page',
      Ia::FIELD_CONTENT => [
        $callout_paragraph,
      ],
      'title' => 'Test Callout component',
      'uid' => $customer_user->id(),
      Ia::FIELD_SITE => $this->getSiteTerm(),
    ]);
    $page_node->save();
    // Set html paragraph as entity be test.
    $referenced_entities = $page_node->get(Ia::FIELD_CONTENT)->referencedEntities();
    return reset($referenced_entities);
  }

}
