<?php

namespace Drupal\bpi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentMappingForm.
 *
 * Defines a form that allows mapping Drupal fields to their BPI counterparts.
 */
class ContentMappingForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bpi_content_mapping';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'bpi.content_mapping',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node_types = node_type_get_names();
    $node_types_names = array_keys($node_types);

    $settings = $this->config('bpi.content_mapping');

    $form['bpi_content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#description' => $this->t('Select a content type into which content from BPI will be syndicated.'),
      '#options' => $node_types,
      '#default_value' => $settings->get('bpi_content_type') ?: reset($node_types_names),
      '#ajax' => [
        'callback' => 'Drupal\bpi\Form\ContentMappingForm::ajaxMappingCallback',
        'wrapper' => 'bpi-field-mapper-wrapper',
        'effect' => 'fade',
        'method' => 'replace',
      ],
    ];

    $default = $form['bpi_content_type']['#default_value'];
    $selected_node_type = $form_state->getValue('bpi_content_type', $default);

    $form['bpi_mapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Field mapping'),
      '#description' => $this->t('Define you custom mapping rules. Each dropdown maps BPI related fields to your content fields.'),
      '#prefix' => '<div id="bpi-field-mapper-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['bpi_mapper']['bpi_field_title'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI title'),
      '#description' => $this->t('Titles are automatically assigned.'),
      '#options' => ['title' => $this->t('Title')],
      '#default_value' => $settings->get('bpi_field_title') ?: 'title',
      '#disabled' => TRUE,
    ];

    $allowed_types = [
      'text_long',
      'text_with_summary',
      'string',
      'entity_reference',
    ];
    $field_instances = bpi_find_field_instances($selected_node_type, $allowed_types);
    array_unshift($field_instances, $this->t('- None -'));

    $form['bpi_mapper']['bpi_field_teaser'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI teaser'),
      '#description' => $this->t('The field to extract the teaser from. If the content type have a body summary, assign it to the body field.'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_teaser') ?: '',
    ];

    $form['bpi_mapper']['bpi_field_body'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI body'),
      '#description' => $this->t('Field to extract the main content from (body field).'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_body') ?: '',
    ];

    $form['bpi_mapper']['bpi_field_tags'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI tags'),
      '#description' => $this->t('Field used to get tags from.'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_tags') ?: '',
    ];

    $form['bpi_mapper']['bpi_field_materials'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI materials'),
      '#description' => $this->t('Field used to get reference to the T!NG data well.'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_materials') ?: '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Custom ajax callback.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return mixed
   *   Form element to render.
   */
  public function ajaxMappingCallback(array &$form, FormStateInterface $form_state) {
    return $form['bpi_mapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('bpi.content_mapping')
      ->set('bpi_content_type', $form_state->getValue('bpi_content_type'))
      ->set('bpi_field_teaser', $form_state->getValue('bpi_field_teaser'))
      ->set('bpi_field_body', $form_state->getValue('bpi_field_body'))
      ->set('bpi_field_tags', $form_state->getValue('bpi_field_tags'))
      ->set('bpi_field_materials', $form_state->getValue('bpi_field_materials'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
