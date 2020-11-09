<?php

namespace Drupal\backup_migrate\Core\Filter;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorTrait;
use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;
use Drupal\backup_migrate\Core\File\BackupFileWritableInterface;

/**
 *
 */
class CompressionFilter extends PluginBase implements FileProcessorInterface {

  use FileProcessorTrait;

  /**
   * Get a list of supported operations and their weight.
   *
   * @return array
   *   A list of operations keyed by the operation's name with a list of
   *   attributes as a nested array.
   */
  public function supportedOps() {
    return [
      'getFileTypes'    => [],
      'backupSettings'  => [],
      'afterBackup'     => ['weight' => 100],
      'beforeRestore'   => ['weight' => -100],
    ];
  }

  /**
   * Return the filetypes supported by this filter.
   */
  public function getFileTypes() {
    return [
      [
        "gzip" => [
          "extension" => "gz",
          "filemime" => "application/x-gzip",
          'ops' => [
            'backup',
            'restore',
          ],
        ],
        "bzip" => [
          "extension" => "bz",
          "filemime" => "application/x-bzip",
          'ops' => [
            'backup',
            'restore',
          ],
        ],
        "bzip2" => [
          "extension" => "bz2",
          "filemime" => "application/x-bzip",
          'ops' => [
            'backup',
            'restore',
          ],
        ],
        "zip" => [
          "extension" => "zip",
          "filemime" => "application/zip",
          'ops' => [
            'backup',
            'restore',
          ],
        ],
      ],
    ];
  }

  /**
   * Get a definition for user-configurable settings.
   *
   * @return array
   */
  public function configSchema(array $params = []) {
    $schema = [];

    if ($params['operation'] == 'backup') {
      $schema['groups']['file'] = [
        'title' => 'Backup File',
      ];
      $compression_options = $this->availableCompressionAlgorithms();
      $schema['fields']['compression'] = [
        'group' => 'file',
        'type' => 'enum',
        'title' => 'Compression',
        'options' => $compression_options,
        'actions' => ['backup'],
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
      'compression' => $this->defaultCompressionAlgorithm(),
    ]);
  }

  /**
   * Run on a backup.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileReadableInterface
   */
  public function afterBackup(BackupFileReadableInterface $file) {
    $out = $success = FALSE;
    if ($this->confGet('compression') == 'gzip') {
      $out = $this->getTempFileManager()->pushExt($file, 'gz');
      $success = $this->gzipEncode($file, $out);
    }
    if ($this->confGet('compression') == 'bzip') {
      $out = $this->getTempFileManager()->pushExt($file, 'bz2');
      $success = $this->bzipEncode($file, $out);
    }
    if ($this->confGet('compression') == 'zip') {
      $out = $this->getTempFileManager()->pushExt($file, 'zip');
      $success = $this->zipEncode($file, $out);
    }

    // If the file was successfully compressed.
    if ($out && $success) {
      $out->setMeta('filesize_uncompressed', $file->getMeta('filesize'));
      $out->setMeta('compression', $this->confGet('compression'));
      return $out;
    }

    // Return the original if we were not able to compress it.
    return $file;
  }

  /**
   * Run on a restore.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $file
   *
   * @return \Drupal\backup_migrate\Core\File\BackupFileReadableInterface
   */
  public function beforeRestore(BackupFileReadableInterface $file) {
    // If the file is not a supported compression type then simply return the
    // same input file.
    $out = $file;

    $type = $file->getExtLast();

    switch (strtolower($type)) {
      case "gz":
      case "gzip":
        $out = $this->getTempFileManager()->popExt($file);
        $this->gzipDecode($file, $out);
        break;

      case "bz":
      case "bz2":
      case "bzip":
      case "bzip2":
        $out = $this->getTempFileManager()->popExt($file);
        $this->bzipDecode($file, $out);
        break;

      case "zip":
        $out = $this->getTempFileManager()->popExt($file);
        $this->zipDecode($file, $out);
        break;
    }
    return $out;
  }

  /**
   * Gzip encode a file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $from
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $to
   *
   * @return bool
   */
  protected function gzipEncode(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $success = FALSE;

    if (!$success && function_exists("gzopen")) {
      if (($fp_out = gzopen($to->realpath(), 'wb9')) && $from->openForRead()) {
        while ($data = $from->readBytes(1024 * 512)) {
          gzwrite($fp_out, $data);
        }
        $success = TRUE;
        $from->close();
        gzclose($fp_out);

        // Get the compressed filesize and set it.
        $fileszc = filesize(\Drupal::service('file_system')->realpath($to->realpath()));
        $to->setMeta('filesize', $fileszc);
      }
    }

    return $success;
  }

  /**
   * Gzip decode a file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $from
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $to
   *
   * @return bool
   */
  protected function gzipDecode(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $success = FALSE;

    if (!$success && function_exists("gzopen")) {
      if ($fp_in = gzopen($from->realpath(), 'rb9')) {
        while (!feof($fp_in)) {
          $to->write(gzread($fp_in, 1024 * 512));
        }
        $success = TRUE;
        gzclose($fp_in);
        $to->close();
      }
    }

    return $success;
  }

  /**
   * BZip encode a file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $from
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $to
   *
   * @return bool
   */
  protected function bzipEncode(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $success = FALSE;
    if (!$success && function_exists("bzopen")) {
      if (($fp_out = bzopen($to->realpath(), 'w')) && $from->openForRead()) {
        while ($data = $from->readBytes(1024 * 512)) {
          bzwrite($fp_out, $data);
        }
        $success = TRUE;
        $from->close();
        bzclose($fp_out);

        // Get the compressed filesize and set it.
        $fileszc = filesize(\Drupal::service('file_system')->realpath($to->realpath()));
        $to->setMeta('filesize', $fileszc);
      }
    }

    return $success;
  }

  /**
   * BZip decode a file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $from
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $to
   *
   * @return bool
   */
  protected function bzipDecode(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $success = FALSE;

    if (!$success && function_exists("bzopen")) {
      if ($fp_in = bzopen($from->realpath(), 'r')) {
        while (!feof($fp_in)) {
          $to->write(bzread($fp_in, 1024 * 512));
        }
        $success = TRUE;
        bzclose($fp_in);
        $to->close();
      }
    }

    return $success;
  }

  /**
   * Zip encode a file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $from
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $to
   *
   * @return bool
   */
  protected function zipEncode(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $success = FALSE;

    if (class_exists('ZipArchive')) {
      $zip = new \ZipArchive();
      $res = $zip->open(\Drupal::service('file_system')->realpath($to->realpath()), constant("ZipArchive::CREATE"));
      if ($res === TRUE) {
        $zip->addFile(\Drupal::service('file_system')->realpath($from->realpath()), $from->getFullName());
      }
      $success = $zip->close();
    }
    // Get the compressed filesize and set it.
    $fileszc = filesize(\Drupal::service('file_system')->realpath($to->realpath()));
    $to->setMeta('filesize', $fileszc);

    return $success;
  }

  /**
   * Gzip decode a file.
   *
   * @param \Drupal\backup_migrate\Core\File\BackupFileReadableInterface $from
   * @param \Drupal\backup_migrate\Core\File\BackupFileWritableInterface $to
   *
   * @return bool
   */
  protected function zipDecode(BackupFileReadableInterface $from, BackupFileWritableInterface $to) {
    $success = FALSE;
    if (class_exists('ZipArchive')) {
      $zip = new \ZipArchive();
      if ($zip->open(\Drupal::service('file_system')->realpath($from->realpath()))) {
        $filename = ($zip->getNameIndex(0));
        if ($fp_in = $zip->getStream($filename)) {
          while (!feof($fp_in)) {
            $to->write(fread($fp_in, 1024 * 512));
          }
          fclose($fp_in);
          $success = $to->close();
        }
      }
      return $success;
    }
  }

  /**
   * Get the compression options as an options array for a form item.
   *
   * @return array
   */
  protected function availableCompressionAlgorithms() {
    $compression_options = ["none" => ("No Compression")];
    if (function_exists("gzencode")) {
      $compression_options['gzip'] = ("GZip");
    }
    if (function_exists("bzcompress")) {
      $compression_options['bzip'] = ("BZip");
    }
    if (class_exists('ZipArchive')) {
      $compression_options['zip'] = ("Zip");
    }
    return $compression_options;
  }

  /**
   * Get the default compression algorithm based on those available.
   *
   * @return string
   *   The machine name of the algorithm.
   */
  protected function defaultCompressionAlgorithm() {
    $available = array_keys($this->availableCompressionAlgorithms());
    // Remove the 'none' option.
    array_shift($available);
    $out = array_shift($available);
    // Return the first available algorithm or 'none' of none other exist.
    return $out ? $out : 'none';
  }

}
