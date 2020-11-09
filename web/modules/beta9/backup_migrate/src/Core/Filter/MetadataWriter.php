<?php

namespace Drupal\backup_migrate\Core\Filter;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\File\BackupFileWritableInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorTrait;
use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\Plugin\PluginCallerInterface;
use Drupal\backup_migrate\Core\Plugin\PluginCallerTrait;

/**
 * Add metadata such as a description to the backup file.
 *
 * @package Drupal\backup_migrate\Core\Filter
 */
class MetadataWriter extends PluginBase implements FileProcessorInterface, PluginCallerInterface {
  use FileProcessorTrait;
  use PluginCallerTrait;

  /**
   * {@inheritdoc}
   */
  public function configSchema(array $params = []) {
    $schema = [];

    // Backup configuration.
    if ($params['operation'] == 'backup') {
      $schema['groups']['advanced'] = [
        'title' => 'Advanced Settings',
      ];
      $schema['fields']['description'] = [
        'group' => 'advanced',
        'type' => 'text',
        'title' => 'Description',
        'multiline' => TRUE,
      ];
    }
    return $schema;
  }

  /**
   * Get the default values for the plugin.
   *
   * @return \Drupal\backup_migrate\Core\Config\Config
   */
  public function configDefaults() {
    return new Config([
      'description' => '',
      'generator' => 'Backup and Migrate',
      'generatorversion' => defined('BACKUP_MIGRATE_CORE_VERSION') ? constant('BACKUP_MIGRATE_CORE_VERSION') : 'unknown',
      'generatorurl' => 'https://github.com/backupmigrate',
      'bam_sourceid' => '',
    ]);
  }

  /**
   * Generate a list of metadata keys to be stored with the backup.
   *
   * @return array
   */
  protected function getMetaKeys() {
    return [
      'description',
      'generator',
      'generatorversion',
      'generatorurl',
      'bam_sourceid',
      'bam_scheduleid',
    ];
  }

  /**
   * Run before the backup/restore begins.
   */
  public function setUp($operand, $options) {
    if ($options['operation'] == 'backup' && $options['source_id']) {
      $this->config()->set('bam_sourceid', $options['source_id']);
      if ($source = $this->plugins()->get($options['source_id'])) {
        // @todo Query the source for it's type and name.
      }
    }
    return $operand;
  }

  /**
   * Run after a backup. Add metadata to the file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileWritableInterface
   */
  public function afterBackup(BackupFileWritableInterface $file) {
    // Add the various metadata.
    foreach ($this->getMetaKeys() as $key) {
      $value = $this->confGet($key);
      $file->setMeta($key, $value);
    }
    return $file;
  }

}
