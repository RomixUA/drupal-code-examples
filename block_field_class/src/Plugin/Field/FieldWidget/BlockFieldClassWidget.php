<?php

namespace Drupal\block_field_class\Plugin\Field\FieldWidget;

use Drupal\block_field\Plugin\Field\FieldWidget\BlockFieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'block_field' widget.
 *
 * @FieldWidget(
 *   id = "block_field_class",
 *   label = @Translation("Block field class"),
 *   field_types = {
 *     "block_field"
 *   }
 * )
 */
class BlockFieldClassWidget extends BlockFieldWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'plugin_id' => '',
      'settings' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    /** @var \Drupal\block_field\BlockFieldItemInterface $item */
    $item =& $items[$delta];
    $element['settings']['classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS class(es)'),
      '#description' => $this->t('Customize the styling of this block by adding CSS classes. Separate multiple classes by spaces.'),
      '#default_value' => isset($item->settings['classes']) ? $item->settings['classes'] : '',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $classes = [];
    foreach ($values as $delta => $value) {
      $classes[$delta] = $value['settings']['classes'];
    }
    $values = parent::massageFormValues($values, $form, $form_state);
    foreach ($values as $delta => &$value) {
      $value['settings']['classes'] = $classes[$delta];
    }

    return $values;
  }

}
