<?php

namespace Drupal\lecapi_admin\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

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

}
