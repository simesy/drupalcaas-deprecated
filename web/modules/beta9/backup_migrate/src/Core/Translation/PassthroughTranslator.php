<?php

namespace Drupal\backup_migrate\Core\Translation;

/**
 * Passes through the us-english strings with the replacement tokens.
 *
 * @package Drupal\backup_migrate\Core\Service
 */
class PassthroughTranslator implements TranslatorInterface {

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
  public function translate($string, $replacements = [], $context = []) {
    // Provide Drupal-like escaping of replacement values.
    foreach ($replacements as $key => $value) {
      switch (substr($key, 0, 1)) {
        case '@':
        case '%':
          $replacements[$key] = strip_tags($value);
          break;
      }
    }

    return strtr($string, $replacements);
  }

}
