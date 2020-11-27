<?php

namespace Drupal\Tests\lecapi\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\lecapi\Ia;
use Drupal\Tests\lecapi\LecapiTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test case for Sequence paragraph.
 */
class LecapiResourceParagraphSequenceTest extends LecapiTestBase {

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
    $request_options[RequestOptions::QUERY]['include'] = implode(',', [Ia::FIELD_ITEMS, Ia::FIELD_SUBTITLE]);
    $response = $this->request('GET', $url, $request_options);
    // Assert response code.
    $this->assertEquals(200, $response->getStatusCode());
    // Assert response body.
    $actual_document = Json::decode($response->getBody()->__toString());
    $this->assertSame('anchor-text', $actual_document['included'][3]['attributes'][Ia::FIELD_ANCHOR]);
    $this->assertSame('Subtitle', $actual_document['included'][3]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('Step 1', $actual_document['included'][0]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('<p>Test markup 1</p>', $actual_document['included'][0]['attributes'][Ia::FIELD_MARKUP]['value']);
    $this->assertSame('Step 2', $actual_document['included'][1]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('<p>Test markup 2</p>', $actual_document['included'][1]['attributes'][Ia::FIELD_MARKUP]['value']);
    $this->assertSame('Step 3', $actual_document['included'][2]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('<p>Test markup 3</p>', $actual_document['included'][2]['attributes'][Ia::FIELD_MARKUP]['value']);
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
    $sequence_items = [
      [
        'heading' => 'Step 1',
        'markup' => '<p>Test markup 1</p>',
      ],
      [
        'heading' => 'Step 2',
        'markup' => '<p>Test markup 2</p>',
      ],
      [
        'heading' => 'Step 3',
        'markup' => '<p>Test markup 3</p>',
      ],
    ];
    $paragraph_sequence_items = [];
    foreach ($sequence_items as $sequence_item) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph_sequence_item */
      $paragraph_sequence_item = $this->entityTypeManager
        ->getStorage('paragraph')
        ->create(['type' => Ia::PG_ITEM]);
      $paragraph_sequence_item->set(Ia::FIELD_HEADING, ['value' => $sequence_item['heading']]);
      $paragraph_sequence_item->set(Ia::FIELD_MARKUP, ['value' => $sequence_item['markup'], 'format' => 'basic']);
      $paragraph_sequence_items[] = $paragraph_sequence_item;
    }
    $sequence_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => Ia::PG_SEQUENCE,
        Ia::FIELD_ITEMS => $paragraph_sequence_items,
        Ia::FIELD_SUBTITLE => $subtitle_paragraph,
      ]);
    /** @var \Drupal\node\Entity\Node $page_node */
    $page_node = $this->drupalCreateNode([
      'type' => 'page',
      Ia::FIELD_CONTENT => [
        $sequence_paragraph,
      ],
      'title' => 'Test Card component',
      'uid' => $customer_user->id(),
      Ia::FIELD_SITE => $this->getSiteTerm(),
    ]);
    $page_node->save();
    // Set html paragraph as entity be test.
    $referenced_entities = $page_node->get(Ia::FIELD_CONTENT)->referencedEntities();
    return reset($referenced_entities);
  }

}
