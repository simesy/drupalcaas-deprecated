<?php

namespace Drupal\backup_migrate\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Settings Profile entities.
 */
class SettingsProfileListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Profile Name');
    $header['id'] = $this->t('Machine name');
    $header['compression'] = $this->t('Compression');
    $header['offline'] = $this->t('Take site offline');
    $header['description'] = $this->t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['compression'] = $entity->config['compressor']['compression'];
    $row['offline'] = $entity->config['utils']['site_offline'];
    $row['description'] = $entity->config['metadata']['description'];
    return $row + parent::buildRow($entity);
  }

}
