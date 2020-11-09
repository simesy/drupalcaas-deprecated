<?php

namespace Drupal\backup_migrate\Drupal\Source;

use Drupal\backup_migrate\Core\Source\MySQLiSource;
use Drupal\backup_migrate\Core\File\BackupFileReadableInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Drupal\Source
 */
class DrupalMySQLiSource extends MySQLiSource {

  /**
   *
   */
  public function importFromFile(BackupFileReadableInterface $file) {
    $num = 0;

    if ($conn = $this->_getConnection()) {
      // Open (or rewind) the file.
      $file->openForRead();

      // Read one line at a time and run the query.
      while ($line = $this->_readSqlCommand($file)) {
        // @todo Why was this commented out?
        // @code
        // if (_backup_migrate_check_timeout()) {
        //   return FALSE;
        // }
        // @endcode
        if ($line) {
          // Execute the sql query from the file.
          $stmt = $conn->prepare($line);
          if (!$stmt) {
            return FALSE;
          }
          $stmt->execute();
          $num++;
        }
      }
      // Close the file, we're done reading it.
      $file->close();
    }
    return $num;
  }

}
