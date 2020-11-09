<?php

namespace Drupal\backup_migrate\Core\Service;

use Drupal\backup_migrate\Core\File\BackupFileWritableInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Service
 */
class TarArchiveWriter implements ArchiveWriterInterface {

  /**
   * @var \Drupal\backup_migrate\Core\File\BackupFileWritableInterface
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
  public function setArchive(BackupFileWritableInterface $out) {
    $this->archive = $out;
  }

  /**
   * {@inheritdoc}
   */
  public function addFile($real_path, $new_path = '') {
    $this->archive->openForWrite(TRUE);

    $new_path = $new_path ? $new_path : $real_path;

    $this->writeHeader($real_path, $new_path);

    $fp = @fopen($real_path, "rb");
    while (($v_buffer = fread($fp, 512)) != '') {
      $v_binary_data = pack("a512", "$v_buffer");
      $this->archive->write($v_binary_data);
    }
    fclose($fp);
  }

  /**
   * @param $real_path
   * @param $new_path
   */
  protected function writeHeader($real_path, $new_path) {
    if (strlen($new_path) > 99) {
      $this->writeLongHeader($new_path);
    }

    $v_info = lstat($real_path);

    $v_uid = sprintf("%6s ", decoct($v_info[4]));
    $v_gid = sprintf("%6s ", decoct($v_info[5]));
    $v_perms = sprintf("%6s ", decoct($v_info['mode']));
    $v_mtime = sprintf("%11s", decoct($v_info['mtime']));

    $v_linkname = '';

    if (@is_link($real_path)) {
      $v_typeflag = '2';
      $v_linkname = readlink($real_path);
      $v_size = sprintf("%11s ", decoct(0));
    }
    elseif (@is_dir($real_path)) {
      $v_typeflag = "5";
      $v_size = sprintf("%11s ", decoct(0));
    }
    else {
      $v_typeflag = '';
      clearstatcache(TRUE, $real_path);
      $v_size = sprintf("%11s ", decoct($v_info['size']));
    }

    $v_magic = '';
    $v_version = '';
    $v_uname = '';
    $v_gname = '';
    $v_devmajor = '';
    $v_devminor = '';
    $v_prefix = '';

    $v_binary_data_first = pack("a100a8a8a8a12A12",
      $new_path, $v_perms, $v_uid,
      $v_gid, $v_size, $v_mtime);
    $v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12",
      $v_typeflag, $v_linkname, $v_magic,
      $v_version, $v_uname, $v_gname,
      $v_devmajor, $v_devminor, $v_prefix, '');

    // Calculate the checksum.
    $v_checksum = 0;
    // First part of the header.
    for ($i = 0; $i < 148; $i++) {
      $v_checksum += ord(substr($v_binary_data_first, $i, 1));
    }
    // Ignore the checksum value and replace it by ' ' (space).
    for ($i = 148; $i < 156; $i++) {
      $v_checksum += ord(' ');
    }
    // Last part of the header.
    for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
      $v_checksum += ord(substr($v_binary_data_last, $j, 1));
    }

    // Write the first 148 bytes of the header in the archive.
    $this->archive->write($v_binary_data_first, 148);

    // Write the calculated checksum.
    $v_checksum = sprintf("%6s ", decoct($v_checksum));
    $v_binary_data = pack("a8", $v_checksum);
    $this->archive->write($v_binary_data, 8);

    // Write the last 356 bytes of the header in the archive.
    $this->archive->write($v_binary_data_last, 356);
  }

  /**
   * @param $new_path
   */
  public function writeLongHeader($new_path) {
    $v_size = sprintf("%11s ", decoct(strlen($new_path)));

    $v_typeflag = 'L';
    $v_linkname = '';
    $v_magic = '';
    $v_version = '';
    $v_uname = '';
    $v_gname = '';
    $v_devmajor = '';
    $v_devminor = '';
    $v_prefix = '';

    $v_binary_data_first = pack("a100a8a8a8a12A12",
      '././@LongLink', 0, 0, 0, $v_size, 0);
    $v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12",
      $v_typeflag, $v_linkname, $v_magic,
      $v_version, $v_uname, $v_gname,
      $v_devmajor, $v_devminor, $v_prefix, '');

    // Calculate the checksum.
    $v_checksum = 0;
    // First part of the header.
    for ($i = 0; $i < 148; $i++) {
      $v_checksum += ord(substr($v_binary_data_first, $i, 1));
    }
    // Ignore the checksum value and replace it by ' ' (space).
    for ($i = 148; $i < 156; $i++) {
      $v_checksum += ord(' ');
    }
    // Last part of the header.
    for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
      $v_checksum += ord(substr($v_binary_data_last, $j, 1));
    }

    // Write the first 148 bytes of the header in the archive.
    $this->archive->write($v_binary_data_first, 148);

    // Write the calculated checksum.
    $v_checksum = sprintf("%6s ", decoct($v_checksum));
    $v_binary_data = pack("a8", $v_checksum);
    $this->archive->write($v_binary_data, 8);

    // Write the last 356 bytes of the header in the archive.
    $this->archive->write($v_binary_data_last, 356);

    // Write the filename as content of the block.
    $i = 0;
    while (($v_buffer = substr($new_path, (($i++) * 512), 512)) != '') {
      $v_binary_data = pack("a512", "$v_buffer");
      $this->archive->write($v_binary_data);
    }
  }

  /**
   * Write a footer to mark the end of the archive.
   */
  private function writeFooter() {
    // Write the last 0 filled block for end of archive.
    $v_binary_data = pack('a1024', '');
    $this->archive->write($v_binary_data);
  }

  /**
   * {@inheritdoc}
   */
  public function closeArchive() {
    $this->writeFooter();
    $this->archive->close();
  }

}
