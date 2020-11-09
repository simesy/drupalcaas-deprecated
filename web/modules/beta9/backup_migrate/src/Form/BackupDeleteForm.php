<?php

namespace Drupal\backup_migrate\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class BackupDeleteForm extends ConfirmFormBase {

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
    return $this->t('Are you sure you want to delete this backup?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will permanently remove %backupId from %destination_name.',
      [
        '%backupId' => $this->backupId,
        '%destination_name' => $this->destination->label(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
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
    return 'backup_migrate_backup_delete_confirm';
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state, $backup_migrate_destination = NULL, $backupId = NULL) {
    $this->destination = $backup_migrate_destination;
    $this->backupId = $backupId;

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
    $destination = $this->destination->getObject();
    $destination->deleteFile($this->backupId);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
