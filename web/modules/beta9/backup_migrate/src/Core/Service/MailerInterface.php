<?php

namespace Drupal\backup_migrate\Core\Service;

/**
 * Interface MailSenderInterface.
 *
 * @package Drupal\backup_migrate\Core\Environment
 */
interface MailerInterface {

  /**
   * @param string|array $to
   *   An RFC 2822 formatted to string or an array of them.
   * @param string $subject
   *   The subject of the email to be sent.
   * @param string $body
   *   The body of the message being sent.
   * @param array $replacements
   *   An array of string replacements for both the body and the subject.
   * @param array $additional_headers
   *   Additional headers to be added to the email if any.
   * @return mixed
   */
  public function send($to, $subject, $body, array $replacements = [], array $additional_headers = []);

}
