<?php

namespace Drupal\commerce_product_variation_select\Plugin\DsField;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Display suite optional page title field.
 *
 * @DsField(
 *   id = "variation_select",
 *   title = @Translation("Variation Select"),
 *   entity_type = "commerce_product",
 *   provider = "commerce_product"
 * )
 */
class VariationSelectDsField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $render = [];
    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->configuration['entity'];
    $variations = [];
    foreach ($product->get('variations') as $variation_item) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
      $variation = $variation_item->entity;
      $variations[$variation->id()] = $variation;
    }
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $current_variation */
    $current_variation = \Drupal::routeMatch()->getParameter('commerce_product_variation');
    $current_variation = empty($variations[$current_variation]) ? reset($variations) : $variations[$current_variation];
    unset($variations[$current_variation->id()]);
    foreach ($variations as $variation_id => $variation) {
      /** @var \Drupal\Core\Field\FieldItemListInterface $field */
      foreach ($variation as $field_name => $field) {
        if (!in_array($field_name, $this->getMetadataVariationFields()) && !$field->equals($current_variation->get($field_name))) {
          $render['#attributes']['class'][] = 'variation-select';
          $render['title'] = [
            '#type' => 'html_tag',
            '#tag' => 'h4',
            '#value' => $this->t('Product types'),
          ];
          $render[$field_name]['#type'] = 'container';
          $render[$field_name]['#attributes']['class'][] = 'variation-field';
          $render[$field_name]['title'] = [
            '#type' => 'html_tag',
            '#tag' => 'h4',
            '#value' => $field->getFieldDefinition()->getLabel(),
          ];
          $render[$field_name]['links']['#type'] = 'container';
          $render[$field_name]['links']['#attributes']['class'][] = 'variation-links';
          $render[$field_name]['links'][$current_variation->id()] = [
            '#type' => 'link',
            '#url' => Url::fromRoute('entity.commerce_product.canonical', [
              'commerce_product' => $product->id(),
              'commerce_product_variation' => $current_variation->id(),
            ]),
            '#title' => $this->getFieldValue($current_variation->get($field_name)),
            '#attributes' => [
              'class' => ['active'],
            ],
          ];
          $render[$field_name]['links'][$variation_id] = [
            '#type' => 'link',
            '#url' => Url::fromRoute('entity.commerce_product.canonical', [
              'commerce_product' => $product->id(),
              'commerce_product_variation' => $variation->id(),
            ]),
            '#title' => $this->getFieldValue($field),
          ];
        }
      }
    }

    return $render;
  }

  /**
   * Get metadata fields which are not characteristics of product.
   *
   * @return array
   *   List of fields name.
   */
  protected function getMetadataVariationFields() {
    return [
      'variation_id',
      'type',
      'uuid',
      'langcode',
      'uid',
      'product_id',
      'sku',
      'title',
      'price',
      'status',
      'created',
      'changed',
    ];
  }

  /**
   * Get variation field value.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   Field item list.
   *
   * @return string
   *   Variation field value
   */
  public function getFieldValue(FieldItemListInterface $field) {
    $value = '';
    // @todo Add get value for other field types.
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if ($entity = $field->entity) {
      $value = $entity->label();
    }
    elseif ($field->getFieldDefinition()->getType() == 'list_string') {
      $values = $field->getFieldDefinition()->getSetting('allowed_values');
      $value = $values[$field->value];
    }
    elseif ($field->value) {
      $value = $field->value;
    }

    return $value;
  }

}
