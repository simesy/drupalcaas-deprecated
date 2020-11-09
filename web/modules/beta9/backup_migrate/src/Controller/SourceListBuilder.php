<?php

namespace Drupal\backup_migrate\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Backup Source entities.
 */
class SourceListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Backup Source');
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
    if ($info = $entity->getPluginDefinition()) {
      $row['type'] = $info['title'];
    }

    return $row + parent::buildRow($entity);
  }

}
