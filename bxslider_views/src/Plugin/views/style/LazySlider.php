<?php

namespace Drupal\bxslider_views\Plugin\views\style;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Style plugin to render each item in a grid cell.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "bx_lazy_slider",
 *   title = @Translation("Lazy Slider"),
 *   help = @Translation("Displays items in a Slider."),
 *   theme = "views_lazy_slider",
 *   display_types = {"normal"}
 * )
 */
class LazySlider extends StylePluginBase {

  /**
   * The block plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   Block manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BlockManagerInterface $block_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->blockManager = $block_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.block'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['items_per_page'] = ['default' => '6'];
    $options['col_class_custom'] = ['default' => ''];
    $options['col_class_default'] = ['default' => TRUE];
    $options['page_class_custom'] = ['default' => ''];
    $options['page_class_default'] = ['default' => TRUE];
    $options['page_block'] = ['default' => ''];
    $options['page_block_class_custom'] = ['default' => ''];
    $options['show_page_block_roles'] = ['default' => []];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['items_per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Items per page'),
      '#default_value' => $this->options['items_per_page'],
      '#required' => TRUE,
      '#min' => 1,
    ];
    $form['col_class_default'] = [
      '#title' => $this->t('Default column classes'),
      '#description' => $this->t('Add the default views column classes like views-col, col-1 and clearfix to the output. You can use this to quickly reduce the amount of markup the view provides by default, at the cost of making it more difficult to apply CSS.'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['col_class_default'],
    ];
    $form['col_class_custom'] = [
      '#title' => $this->t('Custom column class'),
      '#description' => $this->t('Additional classes to provide on each column. Separated by a space.'),
      '#type' => 'textfield',
      '#default_value' => $this->options['col_class_custom'],
    ];
    if ($this->usesFields()) {
      $form['col_class_custom']['#description'] .= ' ' . $this->t('You may use field tokens from as per the "Replacement patterns" used in "Rewrite the output of this field" for all fields.');
    }
    $form['page_class_default'] = [
      '#title' => $this->t('Default page classes'),
      '#description' => $this->t('Adds the default views page classes like views-page, page-1 and clearfix to the output. You can use this to quickly reduce the amount of markup the view provides by default, at the cost of making it more difficult to apply CSS.'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['page_class_default'],
    ];
    $form['page_class_custom'] = [
      '#title' => $this->t('Custom page class'),
      '#description' => $this->t('Additional classes to provide on each page. Separated by a space.'),
      '#type' => 'textfield',
      '#default_value' => $this->options['page_class_custom'],
    ];
    $options = $this->getBlocksList();
    $form['page_block'] = [
      '#title' => $this->t('Page block'),
      '#description' => $this->t('Provide block as slider page.'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->options['page_block'],
    ];
    $form['page_block_class_custom'] = [
      '#title' => $this->t('Custom page block class'),
      '#type' => 'textfield',
      '#states' => [
        'invisible' => [
          'select[name="style_options[page_block]"]' => ['value' => ''],
        ],
      ],
      '#default_value' => $this->options['page_block_class_custom'],
    ];
    $form['show_page_block_roles'] = [
      '#title' => $this->t('When the user has the following roles'),
      '#type' => 'checkboxes',
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', user_role_names()),
      '#states' => [
        'invisible' => [
          'select[name="style_options[page_block]"]' => ['value' => ''],
        ],
      ],
      '#default_value' => $this->options['show_page_block_roles'],
    ];

    if ($this->usesFields()) {
      $form['row_class_custom']['#description'] .= ' ' . $this->t('You may use field tokens from as per the "Replacement patterns" used in "Rewrite the output of this field" for all fields.');
    }
  }

  /**
   * Get block plugins lists.
   *
   * @return array
   *   Block plugins list.
   */
  public function getBlocksList() {
    $definitions = $this->blockManager->getSortedDefinitions();
    $options[''] = $this->t('- None -');
    foreach ($definitions as $plugin_id => $definition) {
      if (isset($definition['context'])) {
        continue;
      }
      $options[$plugin_id] = $definition['admin_label'];
    }

    return $options;
  }

  /**
   * Get block instance.
   *
   * @param string $plugin_id
   *   Block plugin id.
   *
   * @return object
   *   Block instance.
   */
  public function getBlockInstance($plugin_id) {
    if (empty($plugin_id)) {
      return NULL;
    }
    $block_instance = $this->blockManager->createInstance($plugin_id);
    $plugin_definition = $block_instance->getPluginDefinition();
    if ($plugin_definition['id'] == 'broken') {
      return NULL;
    }
    if ($plugin_definition['id'] == 'block_content') {
      $uuid = $block_instance->getDerivativeId();
      if (empty($this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $uuid]))) {
        return NULL;
      }
    }

    return $block_instance;
  }

}
