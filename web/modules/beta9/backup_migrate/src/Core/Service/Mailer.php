<?php

namespace Drupal\backup_migrate\Core\Service;

/**
 * A very basic mailer that uses the php mail function.
 *
 * In most systems this will be replaced by a wrapper around whatever mail
 * library is used in that system.
 *
 * @package Drupal\backup_migrate\Core\Environment
 */
class Mailer implements MailerInterface {

  /**
   * {@inheritdoc}
   */
  public function send($to, $subject, $body, $replacements = [], $additional_headers = []) {
    // Combine the to objects.
    if (is_array($to)) {
      $to = implode(',', $to);
    }

    // Do the string replacement.
    if ($replacements) {
      $subject = strtr($subject, $replacements);
      $body = strtr($body, $replacements);
    }

    // Use the PHP mail function to send the message.
    mail($to, $subject, $body, $additional_headers);
  }

}
