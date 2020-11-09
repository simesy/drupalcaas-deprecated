<?php

namespace Drupal\backup_migrate\Form;

use Drupal\backup_migrate\Drupal\Config\DrupalConfigHelper;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class BackupRestoreForm extends ConfirmFormBase {

  /**
   * @var \Drupal\backup_migrate\Entity\Destination
   */
  public $destination;

  /**
   * @var string
   */
  public $backupId;

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to restore this backup?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Restore');
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return $this->destination->toUrl('backups');
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'backup_migrate_backup_restore_confirm';
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $backup_migrate_destination
   * @param $backupId
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, $backup_migrate_destination = NULL, $backupId = NULL) {
    $this->destination = $backup_migrate_destination;
    $this->backupId = $backupId;

    $bam = backup_migrate_get_service_object();
    $form['source_id'] = DrupalConfigHelper::getPluginSelector($bam->sources(), $this->t('Restore To'));

    $conf_schema = $bam->plugins()->map('configSchema', ['operation' => 'restore']);
    $form += DrupalConfigHelper::buildFormFromSchema($conf_schema, $bam->plugins()->config());
    $form += DrupalConfigHelper::buildAllPluginsForm($bam->plugins(), 'restore');

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $form_state->getValues();
    backup_migrate_perform_restore($config['source_id'], $this->destination->id(), $this->backupId, $config);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
