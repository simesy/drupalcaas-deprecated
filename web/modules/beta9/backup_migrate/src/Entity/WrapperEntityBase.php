<?php

namespace Drupal\backup_migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\Core\Session\AccountInterface;

/**
 * A configuration entity that wraps a Backup and Migrate plugin.
 *
 * This base allows a configuration entity to use any B&M source or destination
 * by using Drupal's plugin system.
 *
 * @package Drupal\backup_migrate\Entity
 */
abstract class WrapperEntityBase extends ConfigEntityBase implements EntityWithPluginCollectionInterface {

  /**
   * The Backup Source ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Backup Source label.
   *
   * @var string
   */
  protected $label;

  /**
   * The plugin collection that holds the block plugin for this entity.
   *
   * @var \Drupal\block\BlockPluginCollection
   */
  protected $pluginCollection;

  /**
   * @return SourcePluginInterface
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public function getObject() {
    if ($plugin = $this->getPlugin()) {
      return $plugin->getObject();
    }
  }

  /**
   * Get the type plugin for this source.
   *
   * @return mixed
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public function getPlugin() {
    if ($this->get('type')) {
      return $this->getPluginCollection()->get($this->get('type'));
    }
    return NULL;
  }

  /**
   * Get the type plugin for this source.
   *
   * @return mixed
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public function getPluginDefinition() {
    if ($plugin = $this->getPlugin()) {
      return $plugin->getPluginDefinition();
    }
    return [];
  }

  /**
   * Gets the plugin collections used by this entity.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection[]
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
   */
  public function getPluginCollections() {
    if ($config = $this->getPluginCollection()) {
      return ['config' => $config];
    }
    return [];
  }

  /**
   * @return \Drupal\block\BlockPluginCollection
   */
  public function getPluginCollection() {
    if ($this->get('type')) {
      if (!$this->pluginCollection) {
        $config = ['name' => $this->get('label')] + (array) $this->get('config');
        $this->pluginCollection = new DefaultSingleLazyPluginCollection(
          $this->getPluginManager(), $this->get('type'), $config);
      }
      return $this->pluginCollection;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($operation == "update" || $operation == "delete") {
      $info = $this->getPluginDefinition();
      if (!empty($info['locked'])) {
        return FALSE;
      }
    }

    return parent::access($operation, $account, $return_as_object);
  }

  /**
   * Return the plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   */
  abstract public function getPluginManager();

}
