<?php

namespace Drupal\backup_migrate\Core\Service;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Core\Tests\Service
 */
class TeeLogger extends AbstractLogger {

  /**
   * @var \Psr\Log\LoggerInterface[]
   */
  protected $loggers;

  /**
   * @param \Psr\Log\LoggerInterface[] $loggers
   */
  public function __construct(array $loggers) {
    $this->setLoggers($loggers);
  }

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   */
  public function log($level, $message, array $context = []) {
    foreach ($this->getLoggers() as $logger) {
      $logger->log($level, $message, $context);
    }
  }

  /**
   * @return \Psr\Log\LoggerInterface[]
   */
  public function getLoggers() {
    return $this->loggers;
  }

  /**
   * @param \Psr\Log\LoggerInterface[] $loggers
   */
  public function setLoggers(array $loggers) {
    $this->loggers = $loggers;
  }

  /**
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function addLogger(LoggerInterface $logger) {
    $this->loggers[] = $logger;
  }

}
