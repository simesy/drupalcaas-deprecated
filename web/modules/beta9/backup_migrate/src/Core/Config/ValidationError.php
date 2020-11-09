<?php

namespace Drupal\backup_migrate\Core\Config;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Config
 */
class ValidationError implements ValidationErrorInterface {

  /**
   * @var string
   */
  protected $fieldKey = '';

  /**
   * @var string
   */
  protected $message = '';

  /**
   * @var array
   */
  protected $replacement = [];

  /**
   * @param $field_key
   * @param $message
   * @param array $replacement
   */
  public function __construct($field_key, $message, array $replacement = []) {
    $this->fieldKey = $field_key;
    $this->message = $message;
    $this->replacement = $replacement;
  }

  /**
   * @return string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @return array
   */
  public function getReplacement() {
    return $this->replacement;
  }

  /**
   * @return string
   */
  public function getFieldKey() {
    return $this->fieldKey;
  }

  /**
   * String representation of the exception.
   *
   * @return string
   *   The string representation of the exception.
   */
  public function __toString() {
    return strtr($this->getMessage(), $this->getReplacement());
  }

}
