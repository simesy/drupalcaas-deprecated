<?php

namespace Drupal\backup_migrate\Core\Translation;

/**
 * An interface for a language translation service.
 *
 * Follows the Drupal translation model where a fully formed English string with
 * replacement tokens is passed in and then localized.
 *
 * Interface TranslatorInterface.
 *
 * @package Drupal\backup_migrate\Core\Service
 */
interface TranslatorInterface {

  /**
   * @param string $string
   *   The string to be translated.
   * @param $replacements
   *   Any untranslatable variables to be replaced into the string.
   * @param $context
   *   Extra context to help translators distinguish ambiguous strings.
   *
   * @return mixed
   */
  public function translate($string, $replacements = [], $context = []);

}
