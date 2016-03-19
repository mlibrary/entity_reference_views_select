<?php

/**
 * Contains \Drupal\entity_reference_views_select\Plugin\Field\FieldWidget\EntityReferenceViewsOptionsButtonsWidget.
 */

namespace Drupal\entity_reference_views_select\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;

/**
 * Plugin implementation of the 'erviews_options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "erviews_options_buttons",
 *   label = @Translation("Entity Reference Views Check boxes/radio buttons"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */
class EntityReferenceViewsOptionsButtonsWidget extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $options = $this->getOptions($items->getEntity());
    $selected = $this->getSelectedOptions($items);
    if ($this->getFieldSettings()['handler'] == 'views') {
      $view = Views::getView($this->getFieldSettings()['handler_settings']['view']['view_name']);
      $view->execute($this->getFieldSettings()['handler_settings']['view']['display_name']);
      foreach ($view->result as $row) {
        $options[$row->_entity->id()] = $options[$row->_entity->id()]->create(\Drupal::service('renderer')->render($view->style_plugin->view->rowPlugin->render($row)));
      }
    }
    // If required and there is one single option, preselect it.
    if ($this->required && count($options) == 1) {
      reset($options);
      $selected = array(key($options));
    }

    if ($this->multiple) {
      $element += array(
        '#type' => 'checkboxes',
        '#default_value' => $selected,
        '#options' => $options,
      );
    }
    else {
      $element += array(
        '#type' => 'radios',
        // Radio buttons need a scalar value. Take the first default value, or
        // default to NULL so that the form element is properly recognized as
        // not having a default value.
        '#default_value' => $selected ? reset($selected) : NULL,
        '#options' => $options,
      );
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if (!$this->required && !$this->multiple) {
      return t('N/A');
    }
  }

}
