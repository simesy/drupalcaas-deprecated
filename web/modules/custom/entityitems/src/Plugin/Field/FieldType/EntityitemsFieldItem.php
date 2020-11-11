<?php

namespace Drupal\entityitems\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;

/**
 * Defines the 'entityitems_field' field type.
 *
 * @FieldType(
 *   id = "entityitems_field",
 *   label = @Translation("Entity items"),
 *   category = @Translation("General"),
 *   default_widget = "entityitems_widget",
 *   default_formatter = "entityitems_formatter"
 * )
 */
class EntityitemsFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
        'backend' => 'geofield_backend_default',
      ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'backend' => 'geofield_backend_default',
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'subtitle' => [
          'type' => 'varchar_ascii',
          'default' => '',
          'length' => 255,
          'not null' => FALSE,
        ],
        'summary' => [
          'type' => 'varchar_ascii',
          'default' => '',
          'length' => 4096,
          'not null' => FALSE,
        ],
        'markup' => [
          'type' => $field_definition->getSetting('case_sensitive') ? 'blob' : 'text',
          'size' => 'big',
        ],
        'uri' => [
          'type' => 'numeric',
          'precision' => 18,
          'scale' => 12,
          'not null' => FALSE,
        ],
        'entity_id' => [
          'description' => 'The ID of the target entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'media_id' => [
          'description' => 'The ID of the media entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
      'indexes' => [
        'entity_id' => ['entity_id'],
        'media_id' => ['media_id'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['subtitle'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Subtitle'))
      ->setRequired(FALSE);

    $properties['summary'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Summary'))
      ->setRequired(FALSE);

    $properties['markup'] =  DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Body text'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(FALSE);

    $properties['uri'] = DataDefinition::create('uri')
      ->setLabel(new TranslatableMarkup('URL'))
      ->setRequired(FALSE);

    $properties['entity_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Entity reference'))
      ->setSetting('unsigned', TRUE);

    $properties['media_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Media reference'))
      ->setSetting('unsigned', TRUE);

    $properties['variant'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Variant'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element['markup'] = [
      '#type' => 'markup',
      '#value' => 'this is the storage class',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    parent::setValue($values);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareCache() {
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $value = [
      'subtitle' => 'A sample thing',
      'summary' => '',
      'markup' => '',
      'uri' => '',
      'entity_id' => NULL,
      'media_id' => NULL,
      'variant' => '',
    ];
    return $value;
  }

}
