<?php

namespace Drupal\Tests\caas\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\caas\Ia;
use Drupal\Tests\caas\CaasTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test case class for paragraph html resource json:api.
 */
class CaasResourceParagraphHtmlTest extends CaasTestBase {

  /**
   * Tests GETting an individual resource.
   */
  public function testGetIndividual() {
    $account_authenticate = $this->drupalCreateUser([], $this->randomMachineName(), TRUE);
    $this->drupalLogin($account_authenticate);
    // Setup entity for testing then mark it for cleanup.
    $entity = $this->setUpTestingEntity();
    $this->markEntityForCleanup($entity);
    // Build and perform request.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', $entity->getEntityTypeId() . '--' . $entity->bundle()), ['entity' => $entity->uuid()]);
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options[RequestOptions::QUERY]['include'] = Ia::FIELD_SUBTITLE;
    $response = $this->request('GET', $url, $request_options);
    // Assert response code.
    $this->assertEquals(200, $response->getStatusCode());
    // Assert response body.
    $actual_document = Json::decode($response->getBody()->__toString());
    $this->assertSame('<p>This is text markup</p>', $actual_document['data']['attributes'][Ia::FIELD_MARKUP]['value']);
    $this->assertSame('<p>This is text markup</p>', $actual_document['data']['attributes'][Ia::FIELD_MARKUP]['processed']);
    $this->assertSame('anchor-text', $actual_document['included'][0]['attributes'][Ia::FIELD_ANCHOR]);
    $this->assertSame('Subtitle', $actual_document['included'][0]['attributes'][Ia::FIELD_HEADING]);
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
    $subtitle_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => 'subtitle',
        Ia::FIELD_ANCHOR => 'anchor-text',
        Ia::FIELD_HEADING => 'Subtitle',
      ]);
    $html_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => 'markup',
        Ia::FIELD_MARKUP => [
          'value' => '<p>This is text markup</p>',
          'format' => 'basic',
        ],
        Ia::FIELD_SUBTITLE => $subtitle_paragraph,
      ]);
    /** @var \Drupal\node\Entity\Node $page_node */
    $page_node = $this->drupalCreateNode([
      'type' => 'page',
      Ia::FIELD_CONTENT => [
        $html_paragraph,
      ],
      'title' => 'Test HTML component',
      'uid' => $customer_user->id(),
      Ia::FIELD_SITE => $this->getSiteTerm(),
    ]);
    $page_node->save();
    // Set html paragraph as entity be test.
    $referenced_entities = $page_node->get(Ia::FIELD_CONTENT)->referencedEntities();
    return reset($referenced_entities);
  }

}
