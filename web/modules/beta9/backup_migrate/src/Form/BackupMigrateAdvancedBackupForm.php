<?php

namespace Drupal\backup_migrate\Form;

use Drupal\backup_migrate\Drupal\Config\DrupalConfigHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for performing a 1-click site backup.
 */
class BackupMigrateAdvancedBackupForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'backup_migrate_ui_manual_backup_advanced';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    // Theme the form if we want it inline.
    // @FIXME
    // $form['#theme'] = 'backup_migrate_ui_manual_quick_backup_form_inline';
    $bam = backup_migrate_get_service_object();

    $form['source'] = [
      '#type' => 'fieldset',
      "#title" => $this->t("Source"),
      "#collapsible" => TRUE,
      "#collapsed" => FALSE,
      "#tree" => FALSE,
    ];
    $form['source']['source_id'] = DrupalConfigHelper::getSourceSelector($bam, $this->t('Backup Source'));
    $form['source']['source_id']['#default_value'] = \Drupal::config('backup_migrate.settings')->get('backup_migrate_source_id');

    $form += DrupalConfigHelper::buildAllPluginsForm($bam->plugins(), 'backup');
    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $filename_token = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['site'],
        '#dialog' => TRUE,
        '#click_insert' => TRUE,
        '#show_restricted' => TRUE,
        '#group' => 'file',
      ];
    }
    else {
      $filename_token = [
        '#type' => 'markup',
        '#markup' => 'In order to use tokens for File Name, please install & enable <a href="https://www.drupal.org/project/token" arget="_blank">Token module</a>. <p></p>',
      ];
    }
    array_splice($form['file'], 4, 0, ['filename_token' => $filename_token]);

    $form['destination'] = [
      '#type' => 'fieldset',
      "#title" => $this->t("Destination"),
      "#collapsible" => TRUE,
      "#collapsed" => FALSE,
      "#tree" => FALSE,
    ];

    $form['destination']['destination_id'] = DrupalConfigHelper::getDestinationSelector($bam, $this->t('Backup Destination'));
    $form['destination']['destination_id']['#default_value'] = \Drupal::config('backup_migrate.settings')->get('backup_migrate_destination_id');

    $form['quickbackup']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Backup now'),
      '#weight' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $bam = backup_migrate_get_service_object($form_state->getValues());

    // Let the plugins validate their own config data.
    if ($plugin_errors = $bam->plugins()->map('configErrors', ['operation' => 'backup'])) {
      foreach ($plugin_errors as $plugin_key => $errors) {
        foreach ($errors as $error) {
          $form_state->setErrorByName($plugin_key . '][' . $error->getFieldKey(), $this->t($error->getMessage(), $error->getReplacement()));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $form_state->getValues();
    backup_migrate_perform_backup($config['source_id'], $config['destination_id'], $config);
  }

}
