<?php

namespace Drupal\Tests\caas\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\caas\Ia;
use Drupal\Tests\caas\CaasTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test case for Cards paragraph.
 */
class CaasResourceParagraphCardsTest extends CaasTestBase {

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
    $this->assertSame('Card item 1', $actual_document['included'][0]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('https://example.com1', $actual_document['included'][0]['attributes'][Ia::FIELD_LINK]['uri']);
    $this->assertSame('Card item 2', $actual_document['included'][1]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('https://example.com2', $actual_document['included'][1]['attributes'][Ia::FIELD_LINK]['uri']);
    $this->assertSame('Card item 3', $actual_document['included'][2]['attributes'][Ia::FIELD_HEADING]);
    $this->assertSame('https://example.com3', $actual_document['included'][2]['attributes'][Ia::FIELD_LINK]['uri']);
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
    $media_image = $this->createMedia(['bundle' => 'image']);
    $media_image->media_image->generateSampleItems();
    $media_image->save();
    $card_items = [
      [
        'heading' => 'Card item 1',
        'link' => 'https://example.com1',
        'media' => $media_image->id(),
      ],
      [
        'heading' => 'Card item 2',
        'link' => 'https://example.com2',
        'media' => $media_image->id(),
      ],
      [
        'heading' => 'Card item 3',
        'link' => 'https://example.com3',
        'media' => $media_image->id(),
      ],
    ];
    $paragraph_card_items = [];
    foreach ($card_items as $card_item) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $card_item_paragraph */
      $paragraph_card_item = $this->entityTypeManager
        ->getStorage('paragraph')
        ->create(['type' => Ia::PG_ITEM]);
      $paragraph_card_item->set(Ia::FIELD_HEADING, ['value' => $card_item['heading']]);
      $paragraph_card_item->set(Ia::FIELD_MEDIA, ['target_id' => $card_item['media']]);
      $paragraph_card_item->set(Ia::FIELD_LINK, ['uri' => $card_item['link']]);
      $paragraph_card_items[] = $paragraph_card_item;
    }
    $cards_paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => 'cards',
        Ia::FIELD_ITEMS => $paragraph_card_items,
        Ia::FIELD_SUBTITLE => $subtitle_paragraph,
      ]);
    /** @var \Drupal\node\Entity\Node $page_node */
    $page_node = $this->drupalCreateNode([
      'type' => 'page',
      Ia::FIELD_CONTENT => [
        $cards_paragraph,
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
