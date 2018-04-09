<?php

namespace Drupal\block_field_class\Plugin\Field\FieldFormatter;

use Drupal\block_field\Plugin\Field\FieldFormatter\BlockFieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'block_field' formatter.
 *
 * @FieldFormatter(
 *   id = "block_field_class",
 *   label = @Translation("Block field class"),
 *   field_types = {
 *     "block_field"
 *   }
 * )
 */
class BlockFieldClassFormatter extends BlockFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      if (!empty($item->settings['classes'])) {
        $elements[$delta]['#attributes']['class'][] = $item->settings['classes'];
      }
    }

    return $elements;
  }

}
