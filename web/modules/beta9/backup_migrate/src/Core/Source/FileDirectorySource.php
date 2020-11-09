<?php

namespace Drupal\backup_migrate\Core\Source;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Exception\BackupMigrateException;
use Drupal\backup_migrate\Core\Exception\IgnorableException;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorInterface;
use Drupal\backup_migrate\Core\Plugin\FileProcessorTrait;
use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\Plugin\PluginCallerInterface;
use Drupal\backup_migrate\Core\Plugin\PluginCallerTrait;
use Drupal\backup_migrate\Core\Service\ArchiveReaderInterface;
use Drupal\backup_migrate\Core\Service\ArchiveWriterInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Source
 */
class FileDirectorySource extends PluginBase implements SourceInterface, FileProcessorInterface, PluginCallerInterface {
  use FileProcessorTrait;
  use PluginCallerTrait;

  /**
   * @var \Drupal\backup_migrate\Core\Service\ArchiveWriterInterface
   */
  private $archiveWriter;

  /**
   * @var \Drupal\backup_migrate\Core\Service\ArchiveReaderInterface
   */
  private $archiveReader;

  /**
   * {@inheritdoc}
   */
  public function supportedOps() {
    return [
      'exportToFile' => [],
      'importFromFile' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function exportToFile() {
    if ($directory = $this->confGet('directory')) {
      // Make sure the directory ends in exactly 1 slash:
      if (substr($directory, -1) !== '/') {
        $directory = $directory . '/';
      }

      if (!$writer = $this->getArchiveWriter()) {
        throw new BackupMigrateException('A file directory source requires an archive writer object.');
      }
      $ext = $writer->getFileExt();
      $file = $this->getTempFileManager()->create($ext);

      if ($files = $this->getFilesToBackup($directory)) {
        $writer->setArchive($file);
        foreach ($files as $new => $real) {
          $writer->addFile($real, $new);
        }
        $writer->closeArchive();
        return $file;
      }
      throw new BackupMigrateException('The directory %dir does not not have any files to be backed up.',
        ['%dir' => $directory]);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function importFromFile(BackupFileReadableInterface $file) {
    if ($directory = $this->confGet('directory')) {
      // Make sure the directory ends in exactly 1 slash:
      if (substr($directory, -1) !== '/') {
        $directory = $directory . '/';
      }

      if (!file_exists($directory)) {
        throw new BackupMigrateException('The directory %dir does not exist to restore to.',
          ['%dir' => $directory]);
      }
      if (!is_writable($directory)) {
        throw new BackupMigrateException('The directory %dir cannot be written to because of the operating system file permissions.',
          ['%dir' => $directory]);
      }

      if (!$reader = $this->getArchiveReader()) {
        throw new BackupMigrateException('A file directory source requires an archive reader object.');
      }
      // Check that the file endings match.
      if ($reader->getFileExt() !== $file->getExtLast()) {
        throw new BackupMigrateException('This source expects a .%ext file.', [
          '%ext' => $reader->getFileExt(),
        ]);
      }

      $reader->setArchive($file);
      $reader->extractTo($directory);
      $reader->closeArchive();

      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get a list if files to be backed up from the given directory.
   *
   * @param string $dir
   *   The name of the directory to list.
   *
   * @return array
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   * @throws \Drupal\backup_migrate\Core\Exception\IgnorableException
   *
   * @internal param $directory
   */
  protected function getFilesToBackup($dir) {
    // Add a trailing slash if there is none.
    if (substr($dir, -1) !== '/') {
      $dir .= '/';
    }

    if (!file_exists($dir)) {
      throw new BackupMigrateException('Directory %dir does not exist.',
        ['%dir' => $dir]);
    }
    if (!is_dir($dir)) {
      throw new BackupMigrateException('The file %dir is not a directory.',
        ['%dir' => $dir]);
    }
    if (!is_readable($dir)) {
      throw new BackupMigrateException('Directory %dir could not be read from.',
        ['%dir' => $dir]);
    }

    // Get a filtered list if files from the directory.
    list($out, $errors) = $this->getFilesFromDirectory($dir);

    // Alert the user to any errors there might have been.
    if ($errors) {
      $count = count($errors);
      $file_list = implode(', ', array_slice($errors, 0, 5));
      if ($count > 5) {
        $file_list .= ', ...';
      }

      if (!$this->confGet('ignore_errors')) {
        throw new IgnorableException('The backup could not be completed because !count files could not be read: (!files).',
          ['!count' => $count, '!files' => $file_list]);
      }
      else {
        // Throw new IgnorableException('!count files could not be read: (!files).', ['!files' => $filesmsg]);.
        // @todo Log the ignored files.
      }
    }

    return $out;
  }

  /**
   * @param $base_path
   *   The name of the directory to list. This must always end in '/'.
   * @param string $subdir
   * @return array
   * @internal param string $dir
   */
  protected function getFilesFromDirectory($base_path, $subdir = '') {
    $out = $errors = [];

    // Open the directory.
    if (!$handle = opendir($base_path . $subdir)) {
      $errors[] = $base_path . $subdir;
    }
    else {
      while (($file = readdir($handle)) !== FALSE) {
        // If not a dot file and the file name isn't excluded.
        if ($file != '.' && $file != '..') {

          // Get the full path of the file.
          $path = $base_path . $subdir . $file;

          // Allow filters to modify or exclude this path.
          $path = $this->plugins()->call('beforeFileBackup', $path, ['source' => $this, 'base_path' => $base_path]);
          if ($path) {
            if (is_dir($path)) {
              list($sub_files, $sub_errors) =
                $this->getFilesFromDirectory($base_path, $subdir . $file . '/');

              // Add the directory if it is empty.
              if (empty($sub_files)) {
                $out[$subdir . $file] = $path;
              }

              // Add the sub-files to the output.
              $out = array_merge($out, $sub_files);
              $errors = array_merge($errors, $sub_errors);
            }
            else {
              if (is_readable($path)) {
                $out[$subdir . $file] = $path;
              }
              else {
                $errors[] = $path;
              }
            }
          }
        }
      }
      closedir($handle);
    }

    return [$out, $errors];
  }

  /**
   * @param \Drupal\backup_migrate\Core\Service\ArchiveWriterInterface $writer
   */
  public function setArchiveWriter(ArchiveWriterInterface $writer) {
    $this->archiveWriter = $writer;
  }

  /**
   * @return \Drupal\backup_migrate\Core\Service\ArchiveWriterInterface
   */
  public function getArchiveWriter() {
    return $this->archiveWriter;
  }

  /**
   * @return \Drupal\backup_migrate\Core\Service\ArchiveReaderInterface
   */
  public function getArchiveReader() {
    return $this->archiveReader;
  }

  /**
   * @param \Drupal\backup_migrate\Core\Service\ArchiveReaderInterface $reader
   */
  public function setArchiveReader(ArchiveReaderInterface $reader) {
    $this->archiveReader = $reader;
  }

  /**
   * Get a definition for user-configurable settings.
   *
   * @param array $params
   *
   * @return array
   */
  public function configSchema(array $params = []) {
    $schema = [];

    // Init settings.
    if ($params['operation'] == 'initialize') {
      $schema['fields']['directory'] = [
        'type' => 'text',
        'title' => $this->t('Directory Path'),
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
      'directory' => '',
    ]);
  }

}
