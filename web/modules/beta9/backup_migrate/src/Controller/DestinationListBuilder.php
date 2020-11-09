<?php

namespace Drupal\backup_migrate\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Backup Destination entities.
 */
class DestinationListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Backup Destination');
    $header['id'] = $this->t('Machine name');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['type'] = $entity->get('type');
    if ($plugin = $entity->getPlugin()) {
      $info = $plugin->getPluginDefinition();
      $row['type'] = $info['title'];
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * Gets this list's default operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operations are for.
   *
   * @return array
   *   The array structure is identical to the return value of
   *   self::getOperations().
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('backups') && $entity->hasLinkTemplate('backups')) {
      $operations['backups'] = [
        'title' => $this->t('List Backups'),
        'weight' => 100,
        'url' => $entity->toUrl('backups'),
      ];
    }

    return $operations;
  }

}
