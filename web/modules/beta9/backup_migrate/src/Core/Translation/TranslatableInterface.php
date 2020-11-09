<?php

namespace Drupal\backup_migrate\Core\Translation;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Translation
 */
interface TranslatableInterface {

  /**
   * Translate a string.
   *
   * @param string $string
   *   The string to be translated.
   * @param $replacements
   *   Any untranslatable variables to be replaced into the string.
   * @param $context
   *   Extra context to help translators distinguish ambiguous strings.
   *
   * @return mixed
   */
  public function t($string, $replacements = [], $context = []);

}
