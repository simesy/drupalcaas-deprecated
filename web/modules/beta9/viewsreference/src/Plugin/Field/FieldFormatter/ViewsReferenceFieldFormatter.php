<?php

namespace Drupal\viewsreference\Plugin\Field\FieldFormatter;

use Drupal\views\Views;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field formatter for Viewsreference Field.
 *
 * @FieldFormatter(
 *   id = "viewsreference_formatter",
 *   label = @Translation("Views reference"),
 *   field_types = {"viewsreference"}
 * )
 */
class ViewsReferenceFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();
    $options['plugin_types'] = ['block'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $types = Views::pluginList();
    $options = [];
    foreach ($types as $key => $type) {
      if ($type['type'] == 'display') {
        $options[str_replace('display:', '', $key)] = $type['title']->render();
      }
    }
    $form['plugin_types'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => $this->t('View display plugins to allow'),
      '#default_value' => $this->getSetting('plugin_types'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $allowed = [];
    $settings = $this->getSettings();
    foreach ($settings['plugin_types'] as $type) {
      if ($type) {
        $allowed[] = $type;
      }
    }
    $summary[] = $this->t('Allowed plugins: @view', ['@view' => implode(', ', $allowed)]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $view_name = $item->getValue()['target_id'];
      $display_id = $item->getValue()['display_id'];
      $data = unserialize($item->getValue()['data'], ['allowed_classes' => FALSE]);
      $view = Views::getView($view_name);
      // Add an extra check because the view could have been deleted.
      if (!is_object($view)) {
        continue;
      }

      $view->setDisplay($display_id);
      $enabled_settings = array_filter($this->getFieldSetting('enabled_settings') ?? []);

      // Add properties to the view so our hook_views_pre_build() implementation
      // can alter the view. This is pretty hacky, but we need this to fix ajax
      // behaviour in views. The hook_views_pre_build() needs to know if the
      // view was part of a viewsreference field or not.
      $view->element['#viewsreference'] = [
        'data' => $data,
        'enabled_settings' => $enabled_settings,
      ];

      $view->preExecute();
      $view->execute($display_id);

      if (!empty($view->result) || !empty($view->empty)) {
        if ($this->getSetting('plugin_types')) {
          // Add a custom template if the title is available.
          $title = $view->getTitle();
          if (!empty($title)) {
            // If the title contains tokens, we need to render the view to
            // populate the rowTokens.
            if (strpos($title, '{{') !== FALSE) {
              $view->render();
              $title = $view->getTitle();
            }
            $elements[$delta]['title'] = [
              '#theme' => 'viewsreference__view_title',
              '#title' => $title,
            ];
          }
        }

        $render_array = $view->buildRenderable($display_id, $view->args, FALSE);

        // The views_add_contextual_links() function needs the following
        // information in the render array in order to attach the contextual
        // links to the view.
        $render_array['#view_id'] = $view->storage->id();
        $render_array['#view_display_show_admin_links'] = $view->getShowAdminLinks();
        $render_array['#view_display_plugin_id'] = $view->getDisplay()->getPluginId();
        views_add_contextual_links($render_array, $render_array['#view_display_plugin_id'], $display_id);

        $elements[$delta]['contents'] = $render_array;
      }
    }
    return $elements;
  }

}
