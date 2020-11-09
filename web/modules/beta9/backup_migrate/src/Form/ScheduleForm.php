<?php

namespace Drupal\backup_migrate\Form;

use Drupal\backup_migrate\Drupal\Config\DrupalConfigHelper;
use Drupal\backup_migrate\Entity\Schedule;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Form
 */
class ScheduleForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $backup_migrate_schedule = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Schedule Name'),
      '#maxlength' => 255,
      '#default_value' => $backup_migrate_schedule->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $backup_migrate_schedule->id(),
      '#machine_name' => [
        'exists' => '\Drupal\backup_migrate\Entity\Schedule::load',
      ],
      '#disabled' => !$backup_migrate_schedule->isNew(),
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Schedule enabled'),
      '#default_value' => $backup_migrate_schedule->get('enabled'),
    ];

    $bam = backup_migrate_get_service_object([], ['nobrowser' => TRUE]);
    $form['source_id'] = DrupalConfigHelper::getSourceSelector(
      $bam,
      t('Backup Source'),
      $backup_migrate_schedule->get('source_id')
    );
    $form['destination_id'] = DrupalConfigHelper::getDestinationSelector(
      $bam,
      t('Backup Destination'),
      $backup_migrate_schedule->get('destination_id')
    );

    $form['settings_profile_id'] = DrupalConfigHelper::getSettingsProfileSelector(
      t('Settings Profile'),
      $backup_migrate_schedule->get('settings_profile_id')
    );

    $period = Schedule::secondsToPeriod($backup_migrate_schedule->get('period'));
    $form['period_container'] = [
      // Reset #parents so the additional container does not appear.
      '#parents' => [],
      '#type' => 'fieldset',
      '#title' => $this->t('Frequency'),
      '#field_prefix' => $this->t('Run every'),
      '#attributes' => [
        'class' => [
          'container-inline',
          'fieldgroup',
          'form-composite',
        ],
      ],
    ];
    $form['period_container']['period_number'] = [
      '#type' => 'number',
      '#default_value' => $period['number'],
      '#min' => 1,
      '#title' => $this->t('Period number'),
      '#title_display' => 'invisible',
      '#size' => 2,
    ];
    $form['period_container']['period_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Period type'),
      '#title_display' => 'invisible',
      '#options' => [],
      '#default_value' => $period['type'],
    ];
    foreach (Schedule::getPeriodTypes() as $key => $type) {
      $form['period_container']['period_type']['#options'][$key] = $type['title'];
    }

    $form['keep'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number to keep'),
      '#default_value' => $backup_migrate_schedule->get('keep'),
      '#description' => $this->t('The number of backups to retain. Once this number is reached, the oldest backup will be deleted to make room for the most recent backup. Leave blank to keep all backups.'),
      '#size' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    // Save period.
    $type = Schedule::getPeriodType($form_state->getValue('period_type'));
    $seconds = Schedule::periodToSeconds([
      'number' => $form_state->getValue('period_number'),
      'type' => $type,
    ]);

    $form_state->setValue('period', $seconds);

    return parent::buildEntity($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $backup_migrate_schedule = $this->entity;
    $status = $backup_migrate_schedule->save();

    switch ($status) {
      case SAVED_NEW:
        \Drupal::messenger()->addMessage($this->t('Created the %label Schedule.', [
          '%label' => $backup_migrate_schedule->label(),
        ]));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved the %label Schedule.', [
          '%label' => $backup_migrate_schedule->label(),
        ]));
    }
    $form_state->setRedirectUrl($backup_migrate_schedule->toUrl('collection'));
  }

}
