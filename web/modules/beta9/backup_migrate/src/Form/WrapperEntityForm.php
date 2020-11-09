<?php

namespace Drupal\backup_migrate\Form;

use Drupal\backup_migrate\Drupal\Config\DrupalConfigHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 *
 * @package Drupal\backup_migrate\Form
 */
class WrapperEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [],
      '#disabled' => !$this->entity->isNew(),
    ];

    if (!$this->entity->get('type')) {
      $form['type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Type'),
      ];
      foreach ($this->entity->getPluginManager()->getDefinitions() as $type) {
        if (empty($type['locked'])) {
          $form['type']['#options'][$type['id']] = $type['title'];
          $form['type'][$type['id']]['#description'] = $type['description'];
        }
      }
    }
    else {
      $type = $this->entity->getPlugin()->getPluginDefinition();
      $form['type'] = [
        '#type' => 'value',
        '#value' => $type['id'],
        '#markup' => $this->t("Type: @type", ['@type' => $type['title']]),
      ];

      if ($bam_plugin = $this->entity->getObject()) {
        $form['config'] = DrupalConfigHelper::buildPluginForm($bam_plugin, 'initialize', ['config']);
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if ($this->entity->isNew()) {
      $actions['submit']['#value'] = $this->t('Save and edit');
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        \Drupal::messenger()->addMessage($this->t('Created %label.', [
          '%label' => $entity->label(),
        ]));
        $form_state->setRedirectUrl($entity->toUrl('edit-form'));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved %label.', [
          '%label' => $entity->label(),
        ]));
        $form_state->setRedirectUrl($entity->toUrl('collection'));
        break;
    }
  }

  /**
   * Override this function.
   *
   * Let it store the config which would otherwise be removed for some reason.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the current form should operate upon.
   * @param array $form
   *   A nested array of form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    foreach ($values as $key => $value) {
      $entity->set($key, $value);
    }
  }

}
