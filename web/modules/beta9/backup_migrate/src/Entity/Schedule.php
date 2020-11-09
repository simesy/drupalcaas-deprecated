<?php

namespace Drupal\backup_migrate\Entity;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\backup_migrate\Core\Destination\ListableDestinationInterface;
use Drupal\backup_migrate\Core\Exception\BackupMigrateException;
use Drupal\backup_migrate\Core\Main\BackupMigrateInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Schedule entity.
 *
 * @ConfigEntityType(
 *   id = "backup_migrate_schedule",
 *   label = @Translation("Schedule"),
 *   module = "backup_migrate",
 *   admin_permission = "administer backup and migrate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\backup_migrate\Controller\ScheduleListBuilder",
 *     "form" = {
 *       "default" = "Drupal\backup_migrate\Form\ScheduleForm",
 *       "delete" = "Drupal\backup_migrate\Form\EntityDeleteForm"
 *     },
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/development/backup_migrate/schedule/edit/{backup_migrate_schedule}",
 *     "delete-form" = "/admin/config/development/backup_migrate/schedule/delete/{backup_migrate_schedule}",
 *     "collection" = "/admin/config/development/backup_migrate/schedule",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "enabled",
 *     "keep",
 *     "period",
 *     "cron",
 *     "source_id",
 *     "destination_id",
 *     "settings_profile_id"
 *   }
 * )
 */
class Schedule extends ConfigEntityBase {

  /**
   * The Schedule ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Schedule label.
   *
   * @var string
   */
  protected $label;

  /**
   * @param \Drupal\backup_migrate\Core\Main\BackupMigrateInterface $bam
   *   The Backup and Migrate service object used to execute the backups.
   *
   * @param bool $force
   *   Run the schedule even if it is not due to be run.
   */
  public function run(BackupMigrateInterface $bam, $force = FALSE) {
    $next_run_at = $this->getNextRun();
    $time = \Drupal::time();
    $should_run_now = ($time->getRequestTime() >= $next_run_at);
    $enabled = $this->get('enabled');
    if ($force || ($should_run_now && $enabled)) {
      // Set the last run time before attempting backup.
      // This will prevent a failing schedule from retrying on every cron run.
      $this->setLastRun($time->getRequestTime());

      try {
        $config = [];
        if ($settings_profile_id = $this->get('settings_profile_id')) {
          // Load the settings profile if one is selected.
          $profile = SettingsProfile::load($settings_profile_id);
          if (!$profile) {
            throw new BackupMigrateException(
              "The settings profile '%profile' does not exist",
              ['%profile' => $settings_profile_id]);
          }
          $config = $profile->get('config');
        }

        \Drupal::logger('backup_migrate')->info(
             "Running schedule %name", ['%name' => $this->get('label')]);
        // @todo Set the config (don't just use the defaults).
        // Run the backup.
        // Set the schedule id in file metadata so that we can delete our own
        // backups later. This requires the metadata writer to have knowledge
        // of 'bam_scheduleid' which is a somewhat tight coupling that I'd like
        // to unwind.
        $config['metadata']['bam_scheduleid'] = $this->id;
        $bam->setConfig(new Config($config));

        $bam->backup($this->get('source_id'), $this->get('destination_id'));

        // Delete old backups.
        if ($keep = $this->get('keep')) {
          $destination = $bam->destinations()->get($this->get('destination_id'));

          // If the destination can be listed then get the list of files.
          if ($destination instanceof ListableDestinationInterface) {
            // Get a list of files to delete. Don't attempt to delete more
            // than 10 files in one go.
            $delete = $destination->queryFiles(
              ['bam_scheduleid' => $this->id],
              'datestamp',
              SORT_DESC,
              10,
              $keep
            );

            foreach ($delete as $file) {
              $destination->deleteFile($file->getFullName());
            }
          }
        }
      }
      catch (BackupMigrateException $e) {
        \Drupal::logger('backup_migrate')->error(
          "Scheduled backup '%name' failed: @err",
          ['%name' => $this->get('label'), '@err' => $e->getMessage()]
        );
      }
    }
  }

  /**
   * @param $timestamp
   *   The unix time this schedule was last run.
   */
  public function setLastRun($timestamp) {
    \Drupal::keyValue('backup_migrate_schedule:last_run')->set($this->id(), $timestamp);
  }

  /**
   * @return int
   *   The unix time this schedule was last run.
   */
  public function getLastRun() {
    return \Drupal::keyValue('backup_migrate_schedule:last_run')->get($this->id());
  }

  /**
   * Get the next time this schedule should run.
   *
   * @return int
   */
  public function getNextRun() {
    $last_run_at = $this->getLastRun();
    if ($last_run_at) {
      return $last_run_at + $this->get('period');
    }
    return \Drupal::time()->getRequestTime() - 1;
  }

  /**
   * Return the schedule frequency formatted for display in human language.
   *
   * @return \Drupal\Core\StringTranslation\PluralTranslatableMarkup
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public function getPeriodFormatted() {
    return Schedule::formatPeriod(Schedule::secondsToPeriod($this->get('period')));
  }

  /**
   * Convert a number of of seconds into a period array.
   *
   * @param int $seconds
   *
   * @return array
   *   An array containing the period definition and the number of them.
   *   ['number' => 123, 'type' => [...]]
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public static function secondsToPeriod($seconds) {
    foreach (array_reverse(Schedule::getPeriodTypes()) as $type) {
      if (($seconds % $type['seconds']) === 0) {
        return ['number' => $seconds / $type['seconds'], 'type' => $type];
      }
    }

    throw new BackupMigrateException('Invalid period.');
  }

  /**
   * Convert a period array into seconds.
   *
   * @param array $period
   *   A period array.
   *
   * @return mixed
   *
   * @throws \Drupal\backup_migrate\Core\Exception\BackupMigrateException
   */
  public static function periodToSeconds(array $period) {
    return $period['number'] * $period['type']['seconds'];
  }

  /**
   * Convert a period array into seconds.
   *
   * @param $period
   *
   * @return \Drupal\Core\StringTranslation\PluralTranslatableMarkup
   */
  public static function formatPeriod($period) {
    return \Drupal::translation()->formatPlural($period['number'], $period['type']['singular'], $period['type']['plural']);
  }

  /**
   * Get a list of available backup periods.
   *
   * Only returns time periods which have a (reasonably) consistent number of
   * seconds (ie: no months).
   *
   * @return array
   */
  public static function getPeriodTypes() {
    return [
      'seconds' => [
        'type' => 'seconds',
        'seconds' => 1,
        'title' => 'Seconds',
        'singular' => 'Once a second',
        'plural' => 'Every @count seconds',
      ],
      'minutes' => [
        'type' => 'minutes',
        'seconds' => 60,
        'title' => 'Minutes',
        'singular' => 'Once a minute',
        'plural' => 'Every @count minutes',
      ],
      'hours' => [
        'type' => 'hours',
        'seconds' => 3600,
        'title' => 'Hours',
        'singular' => 'Hourly',
        'plural' => 'Every @count hours',
      ],
      'days' => [
        'type' => 'days',
        'seconds' => 86400,
        'title' => 'Days',
        'singular' => 'Daily',
        'plural' => 'Every @count days',
      ],
      'weeks' => [
        'type' => 'weeks',
        'seconds' => 604800,
        'title' => 'Weeks',
        'singular' => 'Weekly',
        'plural' => 'Every @count weeks',
      ],
    ];
  }

  /**
   * Get a backup period type given it's key.
   *
   * @param string $type
   *
   * @return array
   */
  public static function getPeriodType($type) {
    return Schedule::getPeriodTypes()[$type];
  }

}
