<?php

namespace Drupal\backup_migrate\Core\Config;

/**
 * Interface ValidationErrorInterface.
 *
 * @package Drupal\backup_migrate\Core\Config
 */
interface ValidationErrorInterface {

  /**
   * @return string
   */
  public function getMessage();

  /**
   * @return array
   */
  public function getReplacement();

  /**
   * @return string
   */
  public function getFieldKey();

}
