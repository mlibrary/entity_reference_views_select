<?php

namespace Drupal\entity_reference_views_select\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;

/**
 * Plugin implementation of the 'erviews_options_select' widget.
 *
 * @FieldWidget(
 *   id = "erviews_options_select",
 *   label = @Translation("Entity Reference Views Select list"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */
class EntityReferenceViewsOptionsSelectWidget extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $options = $this->getOptions($items->getEntity());
    $selected = $this->getSelectedOptions($items, $delta);
    if ($this->getFieldSettings()['handler'] == 'views') {
      $view = Views::getView($this->getFieldSettings()['handler_settings']['view']['view_name']);
      $view->execute($this->getFieldSettings()['handler_settings']['view']['display_name']);
      foreach ($view->result as $row) {
        $options[$row->_entity->id()] = \Drupal::service('renderer')->render($view->style_plugin->view->rowPlugin->render($row));
      }
    }

    $element += array(
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $selected,
      // Do not display a 'multiple' select box if there is only one option.
      '#multiple' => $this->multiple && count($this->options) > 1,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function sanitizeLabel(&$label) {
    // Select form inputs allow unencoded HTML entities, but no HTML tags.
    $label = Html::decodeEntities(strip_tags($label));
  }

  /**
   * {@inheritdoc}
   */
  protected function supportsGroups() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if ($this->multiple) {
      // Multiple select: add a 'none' option for non-required fields.
      if (!$this->required) {
        return t('- None -');
      }
    }
    else {
      // Single select: add a 'none' option for non-required fields,
      // and a 'select a value' option for required fields that do not come
      // with a value selected.
      if (!$this->required) {
        return t('- None -');
      }
      if (!$this->has_value) {
        return t('- Select a value -');
      }
    }
  }

}
