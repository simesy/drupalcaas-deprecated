<?php

namespace Drupal\lecapi\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'image_style_uri' field type.
 *
 * @FieldType(
 *   id = "lecapi_image_style_uri",
 *   label = @Translation("Image style uri"),
 *   description = @Translation("Normalized image style paths"),
 *   no_ui = TRUE,
 *   list_class = "\Drupal\lecapi\Plugin\Field\FieldType\ImageStyleNormalizedFieldItemList",
 * )
 */
class ImageStyleNormalizedFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['urls'] = DataDefinition::create('any')
      ->setLabel(t('URLs'))
      ->setRequired(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('urls')->getValue();
    return $value === serialize([]);
  }

}
