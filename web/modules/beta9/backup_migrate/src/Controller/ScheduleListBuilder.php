<?php

namespace Drupal\backup_migrate\Controller;

use Drupal\backup_migrate\Entity\Schedule;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Exception;

/**
 * Provides a listing of Schedule entities.
 */
class ScheduleListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Schedule Name');
    $header['enabled'] = $this->t('Enabled');
    $header['period'] = $this->t('Frequency');
    $header['last_run'] = $this->t('Last Run');
    $header['next_run'] = $this->t('Next Run');
    $header['keep'] = $this->t('Keep');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * ScheduleListBuilder save implementation requires instance of Schedule.
   * Signature enforced by EntityListBuilder.
   *
   * @throw InvalidArgumentException
   */
  public function buildRow(EntityInterface $entity) {
    if (!$entity instanceof Schedule) {
      throw new Exception();
    }
    $row['label'] = $entity->label();
    $row['enabled'] = $entity->get('enabled') ? $this->t('Yes') : $this->t('No');
    $row['period'] = $entity->getPeriodFormatted();

    $row['last_run'] = $this->t('Never');
    $time = \Drupal::time();
    if ($last_run = $entity->getLastRun()) {
      $row['last_run'] = \Drupal::service('date.formatter')->format($last_run, 'small');
      $row['last_run'] .= ' (' . $this->t('@time ago', ['@time' => \Drupal::service('date.formatter')->formatInterval($time->getRequestTime() - $last_run)]) . ')';
    }

    $row['next_run'] = $this->t('Not Scheduled');
    if (!$entity->get('enabled')) {
      $row['next_run'] = $this->t('Disabled');
    }
    elseif ($next_run = $entity->getNextRun()) {
      $interval = \Drupal::service('date.formatter')->formatInterval(abs($next_run - $time->getRequestTime()));
      if ($next_run > $time->getRequestTime()) {
        $row['next_run'] = \Drupal::service('date.formatter')->format($next_run, 'small');
        $row['next_run'] .= ' (' . $this->t('in @time', ['@time' => $interval]) . ')';
      }
      else {
        $row['next_run'] = $this->t('Next cron run');
        if ($last_run) {
          $row['next_run'] .= ' (' . $this->t('was due @time ago', ['@time' => $interval]) . ')';
        }
      }
    }

    $row['keep'] = \Drupal::translation()->formatPlural($entity->get('keep'), 'Last 1 backup', 'Last @count backups');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    \Drupal::messenger()->addMessage($this->t('The schedule settings have been updated.'));
  }

}
