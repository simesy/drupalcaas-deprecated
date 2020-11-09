<?php

namespace Drupal\backup_migrate\Form;

use Drupal\backup_migrate\Drupal\Config\DrupalConfigHelper;
use Drupal\Component\Utility\Environment;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for performing a 1-click site backup.
 */
class BackupMigrateRestoreForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'backup_migrate_ui_manual_backup_quick';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $bam = backup_migrate_get_service_object();

    $form['backup_migrate_restore_upload'] = [
      '#title' => $this->t('Upload a Backup File'),
      '#type' => 'file',
      '#description' => $this->t("Upload a backup file created by Backup and Migrate. For other database or file backups please use another tool for import. Max file size: %size",
        ["%size" => format_size(Environment::getUploadMaxSize())]
      ),
    ];

    $form['source_id'] = DrupalConfigHelper::getPluginSelector(
      $bam->sources(), $this->t('Restore To'));

    $form += DrupalConfigHelper::buildAllPluginsForm($bam->plugins(), 'restore');

    $form['quickbackup']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Restore now'),
      '#weight' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $form_state->getValues();
    backup_migrate_perform_restore($config['source_id'], 'upload', 'backup_migrate_restore_upload', $config);
  }

}
