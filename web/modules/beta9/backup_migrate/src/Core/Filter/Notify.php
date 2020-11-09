<?php

namespace Drupal\backup_migrate\Core\Filter;

use Drupal\backup_migrate\Core\Plugin\PluginBase;
use Drupal\backup_migrate\Core\Plugin\PluginCallerInterface;
use Drupal\backup_migrate\Core\Plugin\PluginCallerTrait;
use Drupal\backup_migrate\Core\Service\StashLogger;
use Drupal\backup_migrate\Core\Service\TeeLogger;

/**
 * Notifies by email when a backup succeeds or fails.
 *
 * @package Drupal\backup_migrate\Core\Filter
 */
class Notify extends PluginBase implements PluginCallerInterface {
  use PluginCallerTrait;

  /**
   * Add a weight so that our before* operations run before any others.
   *
   * Primarily to ensure this one runs before other plugins have a chance to
   * write any log entries.
   *
   * @return array
   */
  public function supportedOps() {
    return [
      'beforeBackup' => ['weight' => -100000],
      'beforeRestore' => ['weight' => -100000],
    ];
  }

  /**
   * @var \Drupal\backup_migrate\Core\Service\StashLogger
   */
  protected $logstash;

  /**
   *
   */
  public function beforeBackup() {
    $this->addLogger();
  }

  /**
   *
   */
  public function beforeRestore() {
    $this->addLogger();
  }

  /**
   *
   */
  public function backupSucceed() {
    $this->sendNotification('Backup finished sucessfully');
  }

  /**
   *
   */
  public function backupFail(Exception $e) {

  }

  /**
   *
   */
  public function restoreSucceed() {
  }

  /**
   *
   */
  public function restoreFail() {
  }

  /**
   * @param $subject
   * @param $body
   * @param $messages
   */
  protected function sendNotification($subject) {
    $messages = $this->logstash->getAll();
    $body = $subject . "\n";
    if (count($messages)) {

    }
  }

  /**
   * Add the stash logger to the service locator to capture all logged messages.
   */
  protected function addLogger() {
    $services = $this->plugins()->services();

    // Get the current logger.
    $logger = $services->get('Logger');

    // Create a new stash logger to save messages.
    $this->logstash = new StashLogger();

    // Add a tee to send logs to both the regular logger and our stash.
    $services->add('Logger', new TeeLogger([$logger, $this->logstash]));

    // Add the services back into the plugin manager to re-inject existing
    // plugins.
    $this->plugins()->setServiceLocator($services);
  }

  // @todo Add a tee to the logger to capture all messages.
  // @todo Implement backup/restore fail/succeed ops and send a notification.
}
