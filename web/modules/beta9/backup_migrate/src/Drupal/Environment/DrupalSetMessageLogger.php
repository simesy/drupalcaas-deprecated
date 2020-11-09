<?php

namespace Drupal\backup_migrate\Drupal\Environment;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Sends messages to the browser when B&M Migrate is run in interactive mode.
 *
 * @package Drupal\backup_migrate\Drupal\Environment
 */
class DrupalSetMessageLogger extends AbstractLogger {

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   */
  public function log($level, $message, array $context = []) {
    // Translate the PSR logging level to a drupal message type.
    switch ($level) {
      case LogLevel::EMERGENCY:
      case LogLevel::ALERT:
      case LogLevel::CRITICAL:
      case LogLevel::ERROR:
        $type = 'error';
        break;

      case LogLevel::WARNING:
      case LogLevel::NOTICE:
        $type = 'warning';
        break;

      default:
        $type = 'status';
        break;
    }

    // @todo Handle translations properly.
    \Drupal::messenger()->addMessage($message, $type, FALSE);
  }

}
