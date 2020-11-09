<?php

namespace Drupal\backup_migrate\Core\Main;

use Drupal\backup_migrate\Core\Config\ConfigInterface;
use Drupal\backup_migrate\Core\Plugin\PluginManagerInterface;
use Drupal\backup_migrate\Core\Exception\BackupMigrateException;
use Drupal\backup_migrate\Core\Plugin\PluginCallerTrait;
use Drupal\backup_migrate\Core\Plugin\PluginManager;
use Drupal\backup_migrate\Core\Service\ServiceManager;

/**
 * The core Backup and Migrate service.
 */
class BackupMigrate implements BackupMigrateInterface {
  use PluginCallerTrait;

  /**
   * @var \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface
   */
  protected $sources;

  /**
   * @var \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface
   */
  protected $destinations;

  /**
   * @var \Drupal\backup_migrate\Core\Service\ServiceManagerTheservicelocatorforthisobject
   */
  protected $services;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->setServiceManager(new ServiceManager());
    $services = $this->services();

    $services->add('PluginManager', new PluginManager($services));
    $services->add('SourceManager', new PluginManager($services));
    $services->add('DestinationManager', new PluginManager($services));

    // Add these services back into this object using the service manager.
    $services->addClient($this);
  }

  /**
   * {@inheritdoc}
   */
  public function backup($source_id, $destination_id) {
    try {
      // Allow the plugins to set up.
      $this->plugins()->call('setUp', NULL, [
        'operation' => 'backup',
        'source_id' => $source_id,
        'destination_id' => $destination_id,
      ]);

      // Get the source and the destination to use.
      $source = $this->sources()->get($source_id);
      $destinations = [];

      // Allow a single destination or multiple destinations.
      foreach ((array) $destination_id as $id) {
        $destinations[$id] = $this->destinations()->get($id);

        // Check that the destination is valid.
        if (!$destinations[$id]) {
          throw new BackupMigrateException('The destination !id does not exist.', ['!id' => $destination_id]);
        }

        // Check that the destination can be written to.
        // @todo Catch exceptions and continue if at least one dest is valid.
        $destinations[$id]->checkWritable();
      }

      // Check that the source is valid.
      if (!$source) {
        throw new BackupMigrateException('The source !id does not exist.', ['!id' => $source_id]);
      }

      // Run each of the installed plugins which implements the 'beforeBackup'
      // operation.
      $this->plugins()->call('beforeBackup');

      // Do the actual backup.
      $file = $source->exportToFile();

      // Run each of the installed plugins which implements the 'afterBackup'
      // operation.
      $file = $this->plugins()->call('afterBackup', $file);

      // Save the file to each destination.
      foreach ($destinations as $destination) {
        $destination->saveFile($file);
      }

      // Let plugins react to a successful operation.
      $this->plugins()->call('backupSucceed', $file);
    }
    catch (\Exception $e) {
      // Let plugins react to a failed operation.
      $this->plugins()->call('backupFail', $e);

      // The consuming software needs to deal with this.
      throw $e;
    }

    // Allow the plugins to tear down.
    $this->plugins()->call('tearDown', NULL, ['operation' => 'backup', 'source_id' => $source_id, 'destination_id' => $destination_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function restore($source_id, $destination_id, $file_id = NULL) {
    try {
      // Get the source and the destination to use.
      $source = $this->sources()->get($source_id);
      $destination = $this->destinations()->get($destination_id);

      if (!$source) {
        throw new BackupMigrateException('The source !id does not exist.', [
          '!id' => $source_id,
        ]);
      }
      if (!$destination) {
        throw new BackupMigrateException('The destination !id does not exist.', [
          '!id' => $destination_id,
        ]);
      }

      // Load the file from the destination.
      $file = $destination->getFile($file_id);
      if (!$file) {
        throw new BackupMigrateException('The file !id does not exist.', ['!id' => $file_id]);
      }

      // Prepare the file for reading.
      $file = $destination->loadFileForReading($file);
      if (!$file) {
        throw new BackupMigrateException('The file !id could not be opened for reading.', ['!id' => $file_id]);
      }

      // Run each of the installed plugins which implements the 'backup'
      // operation.
      $file = $this->plugins()->call('beforeRestore', $file);

      // Do the actual source restore.
      $import_result = $source->importFromFile($file);
      if (!$import_result) {
        throw new BackupMigrateException('The file could not be imported.');
      }

      // Run each of the installed plugins which implements the 'beforeBackup'
      // operation.
      $this->plugins()->call('afterRestore');

      // Let plugins react to a successful operation.
      $this->plugins()->call('restoreSucceed', $file);
    }
    catch (\Exception $e) {
      // Let plugins react to a failed operation.
      $this->plugins()->call('restoreFail', $e);

      // The consuming software needs to deal with this.
      throw $e;
    }
  }

  /**
   * Set the configuration for the service.
   *
   * This simply passes the configuration on to the plugin manager as all work
   * is done by plugins. This can be called after the service is instantiated to
   * pass new configuration to the plugins.
   *
   * @param \Drupal\backup_migrate\Core\Config\ConfigInterface $config
   */
  public function setConfig(ConfigInterface $config) {
    $this->plugins()->setConfig($config);
  }

  /**
   * Get the list of available destinations.
   *
   * @return \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface
   */
  public function destinations() {
    return $this->destinations;
  }

  /**
   * Set the destinations plugin manager.
   *
   * @param \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface $destinations
   */
  public function setDestinationManager(PluginManagerInterface $destinations) {
    $this->destinations = $destinations;
  }

  /**
   * Get the list of sources.
   *
   * @return \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface
   */
  public function sources() {
    return $this->sources;
  }

  /**
   * Set the sources plugin manager.
   *
   * @param \Drupal\backup_migrate\Core\Plugin\PluginManagerInterface $sources
   */
  public function setSourceManager(PluginManagerInterface $sources) {
    $this->sources = $sources;
  }

  /**
   * Get the service locator.
   *
   * @return \Drupal\backup_migrate\Core\Service\ServiceManager
   */
  public function services() {
    return $this->services;
  }

  /**
   * Set the service locator.
   *
   * @param \Drupal\backup_migrate\Core\Service\ServiceManager $services
   */
  public function setServiceManager(ServiceManager $services) {
    $this->services = $services;
  }

}
