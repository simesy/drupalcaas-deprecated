<?php

namespace Drupal\backup_migrate\Core\Service;

use Drupal\backup_migrate\Core\Exception\BackupMigrateException;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Service
 */
class TarArchiveReader implements ArchiveReaderInterface {

  /**
   * @var \Drupal\backup_migrate\Core\File\BackupFileReadableInterface
   */
  protected $archive;

  /**
   * Get the file extension for this archiver.
   *
   * For a tarball writer this would be 'tar'. For a Zip file writer this would
   * be 'zip'.
   *
   * @return string
   */
  public function getFileExt() {
    return 'tar';
  }

  /**
   * {@inheritdoc}
   */
  public function setArchive(BackupFileReadableInterface $out) {
    $this->archive = $out;
  }

  /**
   * Extract all files to the given directory.
   *
   * @param $directory
   *
   * @return mixed
   */
  public function extractTo($directory) {
    $this->archive->openForRead(TRUE);

    $result = $this->extractAllToDirectory($directory);

    $this->archive->close();

    return $result;
  }

  /**
   * @param $directory
   *   The directory to extract the files to.
   *
   * @return bool
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  private function extractAllToDirectory($directory) {
    clearstatcache();

    // Read a header block.
    while (strlen($block = $this->archive->readBytes(512)) != 0) {
      $header = $this->readHeader($block);
      if (!$header) {
        return FALSE;
      }

      if ($header['filename'] == '') {
        continue;
      }

      // Check for potentially malicious files (containing '..' etc.).
      if ($this->maliciousFilename($header['filename'])) {
        throw new BackupMigrateException(
          'Malicious .tar detected, file %filename. Will not install in desired directory tree',
          ['%filename' => $header['filename']]
        );
      }

      // Ignore extended / pax headers.
      if ($header['typeflag'] == 'x' || $header['typeflag'] == 'g') {
        $this->archive->seekBytes(ceil(($header['size'] / 512)));
        continue;
      }

      // Add the destination directory to the path.
      if (substr($header['filename'], 0, 1) == '/') {
        $header['filename'] = $directory . $header['filename'];
      }
      else {
        $header['filename'] = $directory . '/' . $header['filename'];
      }

      // If the file already exists, make sure we can overwrite it.
      if (file_exists($header['filename'])) {
        // Cannot overwrite a directory with a file.
        if ((@is_dir($header['filename']))
          && ($header['typeflag'] == '')
        ) {
          throw new BackupMigrateException(
            'File %filename already exists as a directory',
            ['%filename' => $header['filename']]
          );
        }
        // Cannot overwrite a file with a directory.
        if (@is_file($header['filename']) && !@is_link($header['filename'])
          && ($header['typeflag'] == "5")
        ) {
          throw new BackupMigrateException(
            'Directory %filename already exists as file',
            ['%filename' => $header['filename']]
          );
        }
        // Cannot overwrite a read-only file.
        if (!is_writable($header['filename'])) {
          throw new BackupMigrateException(
            'File %filename already exists and is write protected',
            ['%filename' => $header['filename']]
          );
        }
      }

      // Extract a directory.
      if ($header['typeflag'] == "5") {
        if (!$this->createDir($header['filename'])) {
          throw new BackupMigrateException(
            'Unable to create directory %filename',
            ['%filename' => $header['filename']]
          );
        }
      }
      // Extract a file/symlink.
      else {
        if (!$this->createDir(dirname($header['filename']))) {
          throw new BackupMigrateException(
            'Unable to create directory for %filename',
            ['%filename' => $header['filename']]
          );
        }

        // Symlink.
        if ($header['typeflag'] == "2") {
          if (@file_exists($header['filename'])) {
            @unlink($header['filename']);
          }
          if (!@symlink($header['link'], $header['filename'])) {
            throw new BackupMigrateException(
              'Unable to extract symbolic link: %filename',
              ['%filename' => $header['filename']]
            );
          }
        }
        // Regular file.
        else {
          // Open the file for writing.
          if (($dest_file = @fopen($header['filename'], "wb")) == 0) {
            throw new BackupMigrateException(
              'Error while opening %filename in write binary mode',
              ['%filename' => $header['filename']]
            );
          }

          // Write the file.
          $n = floor($header['size'] / 512);
          for ($i = 0; $i < $n; $i++) {
            $content = $this->archive->readBytes(512);
            fwrite($dest_file, $content, 512);
          }
          if (($header['size'] % 512) != 0) {
            $content = $this->archive->readBytes(512);
            fwrite($dest_file, $content, ($header['size'] % 512));
          }

          @fclose($dest_file);

          // Change the file mode, mtime.
          @touch($header['filename'], $header['mtime']);
          if ($header['mode'] & 0111) {
            // Make file executable, obey umask.
            $mode = fileperms($header['filename']) | (~umask() & 0111);
            @chmod($header['filename'], $mode);
          }

          clearstatcache();

          // Check if the file exists.
          if (!is_file($header['filename'])) {
            throw new BackupMigrateException(
              'Extracted file %filename does not exist. Archive may be corrupted.',
              ['%filename' => $header['filename']]
            );
          }

          // Check the file size.
          $file_size = filesize($header['filename']);
          if ($file_size != $header['size']) {
            throw new BackupMigrateException(
              'Extracted file %filename does not have the correct file size. File is %actual bytes (%expected bytes expected). Archive may be corrupted',
              [
                '%filename' => $header['filename'],
                '%expected' => (int) $header['size'],
                (int) '%actual' => $file_size,
              ]
            );
          }
        }
      }
    }

    return TRUE;
  }

  /**
   * Create a directory or return true if it already exists.
   *
   * @param $directory
   *
   * @return bool
   */
  private function createDir($directory) {
    if ((@is_dir($directory)) || ($directory == '')) {
      return TRUE;
    }
    $parent = dirname($directory);

    if (
      ($parent != $directory) &&
      ($parent != '') &&
      (!$this->createDir($parent))
    ) {
      return FALSE;
    }
    if (@!mkdir($directory, 0777)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Read a tar file header block.
   *
   * @param $block
   * @param array $header
   *
   * @return array
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  private function readHeader($block, array $header = []) {
    if (strlen($block) == 0) {
      $header['filename'] = '';
      return TRUE;
    }

    if (strlen($block) != 512) {
      $header['filename'] = '';
      throw new BackupMigrateException(
        'Invalid block size: %size bytes',
        ['%size' => strlen($block)]
      );
    }

    if (!is_array($header)) {
      $header = [];
    }

    // Calculate the checksum.
    $checksum = 0;
    // First part of the header.
    for ($i = 0; $i < 148; $i++) {
      $checksum += ord(substr($block, $i, 1));
    }
    // Ignore the checksum value and replace it by ' ' (space).
    for ($i = 148; $i < 156; $i++) {
      $checksum += ord(' ');
    }
    // Last part of the header.
    for ($i = 156; $i < 512; $i++) {
      $checksum += ord(substr($block, $i, 1));
    }

    if (version_compare(PHP_VERSION, "5.5.0-dev") < 0) {
      $fmt = "a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/"
        . "a8checksum/a1typeflag/a100link/a6magic/a2version/"
        . "a32uname/a32gname/a8devmajor/a8devminor/a131prefix";
    }
    else {
      $fmt = "Z100filename/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/"
        . "Z8checksum/Z1typeflag/Z100link/Z6magic/Z2version/"
        . "Z32uname/Z32gname/Z8devmajor/Z8devminor/Z131prefix";
    }
    $data = unpack($fmt, $block);

    if (strlen($data["prefix"]) > 0) {
      $data["filename"] = "$data[prefix]/$data[filename]";
    }

    // Extract the checksum.
    $header['checksum'] = octdec(trim($data['checksum']));
    if ($header['checksum'] != $checksum) {
      $header['filename'] = '';

      // Look for last block (empty block).
      if (($checksum == 256) && ($header['checksum'] == 0)) {
        return $header;
      }

      throw new BackupMigrateException(
        'Invalid checksum for file %filename',
        ['%filename' => $data['filename']]
      );
    }

    // Extract the properties.
    $header['filename'] = rtrim($data['filename'], "\0");
    $header['mode'] = octdec(trim($data['mode']));
    $header['uid'] = octdec(trim($data['uid']));
    $header['gid'] = octdec(trim($data['gid']));
    $header['size'] = octdec(trim($data['size']));
    $header['mtime'] = octdec(trim($data['mtime']));
    if (($header['typeflag'] = $data['typeflag']) == "5") {
      $header['size'] = 0;
    }
    $header['link'] = trim($data['link']);

    // Look for long filename.
    if ($header['typeflag'] == 'L') {
      $header = $this->readLongHeader($header);
    }

    return $header;
  }

  /**
   * Read a tar file header block for files with long names.
   *
   * @param array $header
   *
   * @return array
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  private function readLongHeader(array $header) {
    $filename = '';
    $filesize = $header['size'];
    $n = floor($header['size'] / 512);
    for ($i = 0; $i < $n; $i++) {
      $content = $this->archive->readBytes(512);
      $filename .= $content;
    }
    if (($header['size'] % 512) != 0) {
      $content = $this->archive->readBytes(512);
      $filename .= $content;
    }

    $filename = rtrim(substr($filename, 0, $filesize), "\0");

    // Read the next header.
    $data = $this->archive->readBytes(512);
    $header = $this->readHeader($data, $header);
    $header['filename'] = $filename;

    return $header;
  }

  /**
   * Detect and report a malicious file name.
   *
   * @param string $file
   *
   * @return bool
   */
  private function maliciousFilename($file) {
    if (strpos($file, '/../') !== FALSE) {
      return TRUE;
    }
    if (strpos($file, '../') === 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * This will be called when all files have been added.
   *
   * It gives the implementation a chance to clean up and commit the changes if
   * needed.
   *
   * @return mixed
   */
  public function closeArchive() {
    if ($this->archive) {
      $this->archive->close();
    }
  }

}
