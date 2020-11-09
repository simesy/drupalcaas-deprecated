<?php

namespace Drupal\backup_migrate\Core\Plugin;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Config\ConfigInterface;
use Drupal\backup_migrate\Core\Config\ConfigurableInterface;
use Drupal\backup_migrate\Core\Config\ConfigurableTrait;
use Drupal\backup_migrate\Core\Service\ServiceManager;
use Drupal\backup_migrate\Core\Service\ServiceManagerInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Plugin
 */
class PluginManager implements PluginManagerInterface, ConfigurableInterface {
  use ConfigurableTrait;

  /**
   * @var \Drupal\backup_migrate\Core\Plugin\PluginInterface[]
   */
  protected $items;

  /**
   * @var \Drupal\backup_migrate\Core\Service\ServiceManagerInterface
   */
  protected $services;

  /**
   * @var \Drupal\backup_migrate\Core\File\TempFileManagerInterface
   */
  protected $tempFileManager;

  /**
   * @param \Drupal\backup_migrate\Core\Service\ServiceManagerInterface $services
   * @param \Drupal\backup_migrate\Core\Config\ConfigInterface $config
   */
  public function __construct(ServiceManagerInterface $services = NULL, ConfigInterface $config = NULL) {
    // Add the injected service locator for dependency injection into plugins.
    $this->setServiceManager($services ? $services : new ServiceManager());

    // Set the configuration or a null object if no config was specified.
    $this->setConfig($config ? $config : new Config());

    // Create an array to store the plugins themselves.
    $this->items = [];
  }

  /**
   * Set the configuration. Reconfigure all of the installed plugins.
   *
   * @param \Drupal\backup_migrate\Core\Config\ConfigInterface $config
   */
  public function setConfig(ConfigInterface $config) {
    // Set the configuration object to the one passed in.
    $this->config = $config;

    // Pass the appropriate configuration to each of the installed plugins.
    foreach ($this->getAll() as $key => $plugin) {
      $this->configurePlugin($plugin, $key);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function add($id, PluginInterface $item) {
    $this->preparePlugin($item, $id);
    $this->items[$id] = $item;
  }

  /**
   * {@inheritdoc}
   */
  public function get($id) {
    return isset($this->items[$id]) ? $this->items[$id] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    return empty($this->items) ? [] : $this->items;
  }

  /**
   * Get all plugins that implement the given operation.
   *
   * @param string $op
   *   The name of the operation.
   *
   * @return \Drupal\backup_migrate\Core\Plugin\PluginInterface[]
   */
  public function getAllByOp($op) {
    $out = [];
    $weights = [];

    foreach ($this->getAll() as $key => $plugin) {
      if ($plugin->supportsOp($op)) {
        $out[$key] = $plugin;
        $weights[$key] = $plugin->opWeight($op);
      }
    }
    array_multisort($weights, $out);
    return $out;
  }

  /**
   * {@inheritdoc}
   */
  public function call($op, $operand = NULL, array $params = []) {

    // Run each of the installed plugins which implements the given operation.
    foreach ($this->getAllByOp($op) as $plugin) {
      $operand = $plugin->{$op}($operand, $params);
    }

    return $operand;
  }

  /**
   * {@inheritdoc}
   */
  public function map($op, array $params = []) {
    $out = [];

    // Run each of the installed plugins which implements the given operation.
    foreach ($this->getAllByOp($op) as $key => $plugin) {
      $out[$key] = $plugin->{$op}($params);
    }

    return $out;
  }

  /**
   * Prepare the plugin for use.
   *
   * This is called when a plugin is added to the manager and it configures the
   * plugin according to the config object injected into the manager. It also
   * injects other dependencies as needed.
   *
   * @param \Drupal\backup_migrate\Core\Plugin\PluginInterface $plugin
   *   The plugin to prepare for use.
   * @param string $id
   *   The id of the plugin (to extract the correct settings).
   */
  protected function preparePlugin(PluginInterface $plugin, $id) {
    // If this plugin can be configured, then pass in the configuration.
    $this->configurePlugin($plugin, $id);

    // Inject the available services.
    $this->services()->addClient($plugin);
  }

  /**
   * Set the configuration for the given plugin.
   *
   * @param $plugin
   * @param $id
   */
  protected function configurePlugin(PluginInterface $plugin, $id) {
    // If this plugin can be configured, then pass in the configuration.
    if ($plugin instanceof ConfigurableInterface) {
      // Configure the plugin with the appropriate subset of the configuration.
      $config = (array)$this->confGet($id);

      // Set the config for the plugin.
      $plugin->setConfig(new Config($config));

      // Get the configuration back from the plugin to populate defaults within
      // the manager.
      $this->config()->set($id, $plugin->config());
    }
  }

  /**
   * @return \Drupal\backup_migrate\Core\Service\ServiceManagerInterface
   */
  public function services() {
    return $this->services;
  }

  /**
   * @param \Drupal\backup_migrate\Core\Service\ServiceManagerInterface $services
   */
  public function setServiceManager(ServiceManagerInterface $services) {
    $this->services = $services;

    // Inject or re-inject the services.
    foreach ($this->getAll() as $key => $plugin) {
      $this->services()->addClient($plugin);
    }
  }

}
