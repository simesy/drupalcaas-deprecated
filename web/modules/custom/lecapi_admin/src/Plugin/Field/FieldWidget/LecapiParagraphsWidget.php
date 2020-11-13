<?php

namespace Drupal\lecapi_admin\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\Element;
use Drupal\Core\TypedData\TranslationStatusInterface;
use Drupal\field_group\FormatterHelper;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\Plugin\EntityReferenceSelection\ParagraphSelection;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
    $element['#attributes']['class'][] = 'display-mode-' . $this->getSetting('form_display_mode');
    $element['#attributes']['class'][] = 'paragraphs-type-' . $element['#paragraph_type'];
//    $children = Element::children($element, TRUE);
//    $string = implode(', ', $children);
//    $element['si_label'] = [
//      '#children' => '<p style="color: red; float: right; font-size: 10px; font-family: Monaco,monospace">' . $string . '</p>',
//      '#weight' => -1000,
//    ];
//
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

