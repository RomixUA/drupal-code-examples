<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for views Lazy Slider style templates.
 *
 * Default template: views-lazy-slider.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - view: The view object.
 *   - rows: An array of row items. Each row is an array of content.
 */
function template_preprocess_views_lazy_slider(&$variables) {
  $options = $variables['options'] = $variables['view']->style_plugin->options;
  $page = 0;
  $col = 0;
  $items = [];
  $page_attributes_status = TRUE;
  foreach ($variables['rows'] as $result_index => $item) {
    $items[$page]['content'][$col]['content'] = $item;
    if ($page_attributes_status) {
      $page_attributes = [];
      if ($options['page_class_custom']) {
        $page_attributes['class'][] = $options['page_class_custom'];
      }
      $items[$page]['attributes'] = new Attribute($page_attributes);
      $page_attributes_status = FALSE;
    }
    $col_attributes = [];
    if ($options['col_class_custom']) {
      $col_attributes['class'][] = $options['col_class_custom'];
    }
    $items[$page]['content'][$col]['attributes'] = new Attribute($col_attributes);
    if ($col + 1 >= $options['items_per_page']) {
      $col = 0;
      $page_attributes_status = TRUE;
      $page++;
    }
    else {
      $col++;
    }
  }
  if (count($variables['rows']) % $options['items_per_page']) {
    $page++;
  }
  $roles = \Drupal::currentUser()->getRoles();
  if ($options['page_block'] && (empty($roles) || array_intersect($roles, $variables['options']['show_page_block_roles']))) {
    /** @var \Drupal\Core\Block\BlockPluginInterface $block_instance */
    $block_instance = $variables['view']->style_plugin->getBlockInstance($options['page_block']);
    if ($block_instance && $block_instance->access(\Drupal::currentUser())) {
      $items[$page]['content'][0]['content'] = [
        '#theme' => 'block',
        '#attributes' => [],
        '#configuration' => $block_instance->getConfiguration(),
        '#plugin_id' => $block_instance->getPluginId(),
        '#base_plugin_id' => $block_instance->getBaseId(),
        '#derivative_plugin_id' => $block_instance->getDerivativeId(),
        '#id' => $options['page_block'],
        'content' => $block_instance->build(),
      ];
      $page_attributes = [];
      $page_block_attributes = [];
      if ($options['page_class_custom']) {
        $page_attributes['class'][] = $options['page_class_custom'];
      }
      if ($options['page_block_class_custom']) {
        $page_block_attributes['class'][] = $options['page_block_class_custom'];
      }
      $items[$page]['attributes'] = new Attribute($page_attributes);
      $items[$page]['content'][0]['attributes'] = new Attribute($page_block_attributes);
    }
  }
  $variables['items'] = $items;
}
