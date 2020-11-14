<?php

namespace Drupal\lecapi_admin\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;
use Drupal\lecapi\Ia;

/**
 * Plugin implementation of the 'entity_reference_revisions item_paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "lecapi_paragraphs",
 *   label = @Translation("Enhanced Paragraph Widget"),
 *   description = @Translation("Enhanced paragraph widget for the Lil Engine Content API site."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class LecapiParagraphsWidget extends ParagraphsWidget {

  /**
   * {@inheritdoc}
   *
   * Adds a templating wrapper.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Add a class to show not just the paragraph type, but also the chosen display mode used in the widget settings.
    $element['#attributes']['class'][] = 'lecapi-paragraphs-subform';
    $element['#attributes']['class'][] = 'paragraph-form-' . $element['#paragraph_type'] . '-' . $this->getSetting('form_display_mode');
    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Set some defaults that are consistent across the site.
   */
  public static function defaultSettings() {
    $defaults = parent::defaultSettings();
    $defaults['title'] = $defaults['title_plural'] = 'Content';
    // By default the forms should be closed and using a preview mode. (Items default to open as they'll be closed if the parent is closed.)
    $defaults['closed_mode'] = 'preview';
    $defaults['edit_mode'] = 'closed';
    return $defaults;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    return $elements;

    $allowed_types = $this->getAllowedTypes();
    if (count($allowed_types) == 1 && isset($allowed_types[Ia::PG_ITEM])) {
      // The only allowed paragraph type is a a reusable item, so offer extra features.
      $form_display_mode = $this->getSetting('form_display_mode');
      $paragraph_type = ParagraphsType::load(Ia::PG_ITEM);

      $field_map = \Drupal::entityManager()->getFieldMap();
      $node_field_map = $field_map['node'];
      $node_fields = array_keys($node_field_map['node']);
    }




    $elements['form_display_mode'] = array(
      '#type' => 'select',
      '#options' => \Drupal::service('entity_display.repository')->getFormModeOptions($this->getFieldSetting('target_type')),
      '#description' => $this->t('The form display mode to use when rendering the paragraph form.'),
      '#title' => $this->t('Form display mode'),
      '#default_value' => $this->getSetting('form_display_mode'),
      '#required' => TRUE,
    );

    $options  = [];
    foreach ($this->getAllowedTypes() as $key => $bundle) {
      $options[$key] = $bundle['label'];
    }

    $elements['default_paragraph_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Default paragraph type'),
      '#empty_value' => '_none',
      '#default_value' => $this->getDefaultParagraphTypeMachineName(),
      '#options' => $options,
      '#description' => $this->t('When creating a new host entity, a paragraph of this type is added.'),
    ];

    $elements['features'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable widget features'),
      '#options' => $this->getSettingOptions('features'),
      '#default_value' => $this->getSetting('features'),
      '#description' => $this->t('When editing, available as action. "Add above" only works in add mode "Modal form"'),
      '#multiple' => TRUE,
    ];

  }

}
