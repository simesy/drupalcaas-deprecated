<?php

namespace Drupal\backup_migrate\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Form
 */
class DestinationForm extends WrapperEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    $form['label']['#description'] = $this->t("Label for the Backup Destination.");
    $form['id']['#machine_name']['exists'] = '\Drupal\backup_migrate\Entity\Destination::load';

    return $form;
  }

}
