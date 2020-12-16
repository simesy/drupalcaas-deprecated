<?php

namespace Drupal\lecapi\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Represents the computed image styles for a file entity.
 */
class ImageStyleNormalizedFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $entity = $this->getEntity();
    $uri = ($entity instanceof File) ? $entity->getFileUri() : FALSE;
    $styles = ImageStyle::loadMultiple();
    $urls = [];
    $urls['original'] = file_create_url($uri);
    foreach ($styles as $name => $style) {
      if ($style instanceof ImageStyle) {
        $urls[$name] = $style->buildUrl($uri);
      }
    }
    $this->list[0] = $this->createItem(0, ['urls' => $urls]);
  }

}
