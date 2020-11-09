<?php

namespace Drupal\backup_migrate\Drupal\Filter;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorTrait;
use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;
use Drupal\backup_migrate\Core\File\BackupFileWritableInterface;
use Defuse\Crypto\File as CryptoFile;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\Filter
 */
class DrupalEncrypt extends PluginBase implements FileProcessorInterface {

  use FileProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function configSchema(array $params = []) {
    $schema = [];

    // Backup configuration.
    if ($params['operation'] == 'backup' || $params['operation'] == 'restore') {

      if (class_exists('\Defuse\Crypto\File')) {
        $schema['groups']['encrypt'] = [
          'title' => 'Backup Encryption',
        ];
        $schema['fields']['encrypt'] = [
          'group' => 'encrypt',
          'type' => 'boolean',
          'title' => $params['operation'] == 'backup' ? $this->t('Encrypt File') : $this->t('Decrypt file'),
          'description' => $this->t('Password for encrypting / decrypting the file'),
        ];
        $schema['fields']['encrypt_password'] = [
          'group' => 'encrypt',
          'type' => 'password',
          'title' => $params['operation'] == 'backup' ? $this->t('Encryption Password') : $this->t('Decryption Password'),
        ];
      }
      else {
        \Drupal::messenger()->addMessage($this->t('Please install the Defuse PHP-encryption library via Composer to be able to encrypt backup files.'), 'warning');
      }
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
      'encrypt' => FALSE,
    ]);
  }

  /**
   *
   */
  protected function encryptFile(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $path = \Drupal::service('file_system')->realpath($from->realpath());
    $out_path = \Drupal::service('file_system')->realpath($to->realpath());

    try {
      CryptoFile::encryptFileWithPassword($path, $out_path, $this->confGet('encrypt_password'));
      $fileszc = filesize(\Drupal::service('file_system')->realpath($to->realpath()));
      $to->setMeta('filesize', $fileszc);
      return TRUE;
    }
    catch (Exception $e) {
      return FALSE;
    }
  }

  /**
   *
   */
  protected function decryptFile(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $path = \Drupal::service('file_system')->realpath($from->realpath());
    $out_path = \Drupal::service('file_system')->realpath($to->realpath());

    try {
      CryptoFile::decryptFileWithPassword($path, $out_path, $this->confGet('encrypt_password'));

      return TRUE;
    }
    catch (Exception $e) {
      return FALSE;
    }
  }

  /**
   *
   */
  public function beforeRestore(BackupFileReadableInterface $file) {
    $type = $file->getExtLast();
    if ($type == 'ssl' && $this->confGet('encrypt')) {
      $out = $this->getTempFileManager()->popExt($file);
      $success = $this->decryptFile($file, $out);
      if ($out && $success) {
        return $out;
      }
    }

    return $file;
  }

  /**
   *
   */
  public function supportedOps() {
    return [
      'getFileTypes' => [],
      'backupSettings' => [],
      'afterBackup' => ['weight' => 1000],
      'beforeRestore' => ['weight' => -1000],
    ];
  }

  /**
   *
   */
  public function afterBackup(BackupFileReadableInterface $file) {
    if ($this->confGet('encrypt')) {
      $out = $this->getTempFileManager()->pushExt($file, 'ssl');
      $success = $this->_encryptFile($file, $out);
      if ($out && $success) {
        return $out;
      }
    }

    return $file;
  }

}
