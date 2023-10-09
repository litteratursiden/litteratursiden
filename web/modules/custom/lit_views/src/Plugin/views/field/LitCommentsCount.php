<?php

namespace Drupal\lit_views\Plugin\views\field;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\NumericField;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the number of the comments.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("lit_comments_count")
 */
final class LitCommentsCount extends NumericField {

  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   Database Service Object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('database'));
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['field_name'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field name'),
      '#default_value' => $this->options['field_name'],
      '#description' => $this->t('The comment field name.'),
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
    $this->field_alias = $this->table . '_' . $this->field;
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$values) {
    $nids = [];
    $ids = [];
    foreach ($values as $id => $result) {
      $nids[] = $result->nid;
      $values[$id]->{$this->field_alias} = 0;
      // Create a reference so we can find this record in the values again.
      if (empty($ids[$result->nid])) {
        $ids[$result->nid] = [];
      }
      $ids[$result->nid][] = $id;
    }

    if ($nids) {
      $result = $this->database->query('SELECT entity_id, comment_count FROM {comment_entity_statistics} WHERE entity_id IN ( :entity_ids[] ) AND field_name = :field_name', [
        ':entity_ids[]' => $nids,
        ':field_name' => $this->options['field_name'],
      ])
        ->fetchAll();

      foreach ($result as $statistic) {
        foreach ($ids[$statistic->entity_id] as $id) {
          $values[$id]->{$this->field_alias} = $statistic->comment_count;
        }
      }
    }
  }

}
