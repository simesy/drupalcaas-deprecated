<?php

namespace Drupal\Tests\caas\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\caas\Ia;
use Drupal\Tests\caas\CaasTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test case for List paragraph.
 */
class CaasResourceParagraphListTest extends CaasTestBase {

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
    $this->assertSame('Item 1', $actual_document['included'][0]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('<p>Markup item 1</p>', $actual_document['included'][0]['attributes'][Ia::FIELD_MARKUP]['value']);
    $this->assertSame('Item 2', $actual_document['included'][1]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('<p>Markup item 2</p>', $actual_document['included'][1]['attributes'][Ia::FIELD_MARKUP]['value']);
    $this->assertSame('Item 3', $actual_document['included'][2]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('<p>Markup item 3</p>', $actual_document['included'][2]['attributes'][Ia::FIELD_MARKUP]['value']);
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
    $list_items = [
      [
        'heading' => 'Item 1',
        'markup' => '<p>Markup item 1</p>',
      ],
      [
        'heading' => 'Item 2',
        'markup' => '<p>Markup item 2</p>',
      ],
      [
        'heading' => 'Item 3',
        'markup' => '<p>Markup item 3</p>',
      ],
    ];
    $paragraph_list_items = [];
    foreach ($list_items as $list_item) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph_list_item */
      $paragraph_list_item = $this->entityTypeManager
        ->getStorage('paragraph')
        ->create(['type' => Ia::PG_ITEM]);
      $paragraph_list_item->set(Ia::FIELD_HEADING, ['value' => $list_item['heading']]);
      $paragraph_list_item->set(Ia::FIELD_MARKUP, ['value' => $list_item['markup'], 'format' => 'basic']);
      $paragraph_list_items[] = $paragraph_list_item;
    }
    $list_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => 'list',
        Ia::FIELD_ITEMS => $paragraph_list_items,
        Ia::FIELD_SUBTITLE => $subtitle_paragraph,
      ]);
    /** @var \Drupal\node\Entity\Node $page_node */
    $page_node = $this->drupalCreateNode([
      'type' => 'page',
      Ia::FIELD_CONTENT => [
        $list_paragraph,
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
