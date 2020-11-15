<?php

namespace Drupal\lecapi\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Test plugin with field selection.
 *
 * @ParagraphsBehavior(
 *   id = "lecapi_variants",
 *   label = @Translation("Component variants"),
 *   description = @Translation("Allows selecting variants for component types."),
 *   weight = 0
 * )
 */
class LecapiComponentVariants extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['variants'] = [
      '#title' => $this->t('Variant options'),
      '#type' => 'textarea',
      '#default_value' => $this->configuration['variants'],
      '#rows' => 5,
      '#description' => $this->t("Available variants for this type. Use the format of key|Label."),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $variants = $form_state->getValue('variants');
    $this->configuration['variants'] = $variants;
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'variants' => "default|No variations available",
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $all_options = $this->getVariantOptions();
    $form['variant'] = [
      '#type' => 'select',
      '#title' => 'Select a variant for this paragraph',
      '#options' => $all_options,
      '#default_value' => $paragraph->getBehaviorSetting($this->pluginId, 'variant', reset($all_options))
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraphs_entity, EntityViewDisplayInterface $display, $view_mode) {}

  private function getVariantOptions() {
    $return = [];
    $options = preg_split("(\r\n?|\n)", $this->configuration['variants']);
    foreach ($options as $option) {
      list($key, $value) = explode('|', $option);
      $return[$key] = $value;
    }
    return $return;
  }

}
