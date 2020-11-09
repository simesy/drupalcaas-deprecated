<?php

namespace Drupal\backup_migrate\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Form
 */
class SourceForm extends WrapperEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    $form['label']['#description'] = $this->t("Label for the Backup Source.");
    $form['id']['#machine_name']['exists'] = '\Drupal\backup_migrate\Entity\Source::load';

    return $form;
  }

}
