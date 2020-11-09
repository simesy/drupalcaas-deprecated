<?php

namespace Drupal\backup_migrate\Core\Service;

use Psr\Log\AbstractLogger;

/**
 * Saves log entries to memory to be processed during the current process.
 *
 * This simple service does no clearing or memory management so should not be
 * used for a long-running process.
 *
 * @package Drupal\backup_migrate\Core\Service
 */
class StashLogger extends AbstractLogger {

  /**
   * @var array
   */
  protected $logs = [];

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   */
  public function log($level, $message, array $context = []) {
    $this->logs[] = [
      'level' => $level,
      'message' => $message,
      'context' => $context,
    ];
  }

  /**
   * Get all of the log messages that were saved to this stash.
   *
   * @return array
   */
  public function getAll() {
    return $this->logs;
  }

}
