<?php

namespace Drupal\backup_migrate\Core\Translation;

/**
 * This trait can be used to implement the TranslatableInterface.
 *
 * A class with this trait will be able to use
 *  $this->t(...);
 * to translate a string (if a translator is available).
 *
 * @package Drupal\backup_migrate\Core\Translation
 */
trait TranslatableTrait {

  /**
   * @var TranslatorInterface
   */
  protected $translator;

  /**
   * @param TranslatorInterface $translator
   */
  public function setTranslator(TranslatorInterface $translator) {
    $this->translator = $translator;
  }

  /**
   * Translate the given string if there is a translator service available.
   *
   * @param $string
   * @param $replacements
   * @param $context
   *
   * @return mixed
   */
  public function t($string, $replacements = [], $context = []) {
    // If there is no translation service available use a passthrough to send
    // back the original (en-us) string.
    if (empty($this->translator)) {
      $this->translator = new PassthroughTranslator();
    }
    return $this->translator->translate($string, $replacements, $context);
  }

}
