<?php

namespace Drupal\backup_migrate\Core\Source;

use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorTrait;
use Drupal\backup_migrate\Core\Plugin\PluginBase;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Source
 */
abstract class SourceBase extends PluginBase implements SourceInterface, FileProcessorInterface {
  use FileProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function supportedOps() {
    return [
      'exportToFile' => [],
      'importFromFile' => [],
    ];
  }

}
