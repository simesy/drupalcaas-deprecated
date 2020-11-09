<?php

namespace Drupal\backup_migrate\Core\Exception;

use Exception;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Exception
 */
class BackupMigrateException extends Exception {
  protected $replacement = [];
  protected $messageRaw = 'Unknown exception';

  /**
   * Construct the exception. Note: The message is NOT binary safe.
   *
   * @link http://php.net/manual/en/exception.construct.php
   *
   * @param string $message
   *   [optional] The Exception message to throw.
   * @param array $replacement
   *   [optional] Untranslatable values to replace into the string.
   * @param int $code
   *   [optional] The Exception code.
   */
  public function __construct($message = NULL, array $replacement = [], $code = 0) {
    $this->replacement = $replacement;
    $this->messageRaw = $message;

    // Send the replaced message to the parent constructor to act as normal in
    // most cases.
    parent::__construct(strtr($message, $replacement), $code);
  }

  /**
   * Get the unmodified message with replacement tokens.
   *
   * @return null|string
   */
  public function getMessageRaw() {
    return $this->messageRaw;
  }

}
