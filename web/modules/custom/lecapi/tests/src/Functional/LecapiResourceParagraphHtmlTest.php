<?php

namespace Drupal\Tests\lecapi\Functional;

use Drupal\Core\Url;
use Drupal\lecapi\Ia;
use Drupal\Tests\lecapi\LecapiResourceTestBase;

/**
 * Test case class for paragraph html resource json:api.
 */
class LecapiResourceParagraphHtmlTest extends LecapiResourceTestBase {

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

  /**
   * Get expected response.
   *
   * @return array
   *   Return a expected response array.
   */
  protected function getExpectedResponse() {
    $self_url = Url::fromUri('base:/jsonapi/' . $this->entity->getEntityTypeId() . '/' . $this->entity->bundle() . '/' . $this->entity->uuid())
      ->setAbsolute()
      ->toString(TRUE)->getGeneratedUrl();
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

}
