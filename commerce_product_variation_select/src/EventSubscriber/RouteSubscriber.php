<?php

namespace Drupal\commerce_product_variation_select\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.commerce_product.canonical')) {
      $route->setPath('/product/{commerce_product}/{commerce_product_variation}');
      $route->setDefault('commerce_product_variation', NULL);
      $route->setRequirement('commerce_product_variation', '\d+');
      $options = $route->getOption('parameters');
      $options['commerce_product_variation']['type'] = 'entity:commerce_product_variation';
    }
  }

}