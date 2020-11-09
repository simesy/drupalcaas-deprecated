<?php

namespace Drupal\backup_migrate\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to delete config entities.
 */
class EntityDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t(
      'Are you sure you want to delete %name?',
      ['%name' => $this->entity->label()]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl('collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    \Drupal::messenger()->addMessage(
      $this->t('Deleted @label.', ['@label' => $this->entity->label()])
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
