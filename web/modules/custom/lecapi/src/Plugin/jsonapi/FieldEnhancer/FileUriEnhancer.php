<?php

namespace Drupal\lecapi\Plugin\jsonapi\FieldEnhancer;

use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerBase;
use Shaper\Util\Context;

/**
 * Perform additional manipulations to uri fields.
 *
 * @ResourceFieldEnhancer(
 *   id = "file_uri",
 *   label = @Translation("Custom response file uri field."),
 *   description = @Translation("Add fully_qualified_url to URI Field."),
 * )
 */
class FileUriEnhancer extends ResourceFieldEnhancerBase {

  /**
   * {@inheritdoc}
   */
  protected function doTransform($data, Context $context) {
    if (isset($data['fully_qualified_url'])) {
      unset($data['fully_qualified_url']);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($data, Context $context) {
    if ($data['value']) {
      $data['fully_qualified_url'] = file_create_url($data['value']);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getOutputJsonSchema() {
    return [
      'type' => 'any',
    ];
  }

}
