<?php

namespace Drupal\lit_xml\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Class LitXmlSettingsForm.
 */
class LitXmlSettingsForm extends ConfigFormBase {

  /**
   * @const array
   */
  protected static $types = [
    'analysis',
    'article',
    'author_portrait',
    'book_list',
    'interview',
    'review'
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lit_xml_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lit_xml.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lit_xml.settings');

    $form['content_type'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('What to export'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE
    ];

    $form['content_type']['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content type that should be exported'),
      '#required' => TRUE,
      '#options' => $this->types(),
      '#description' => $this->t('The selected content types will be exported at next run.'),
      '#default_value' => $config->get('types') ?: [],
    ];

    $form['content_type']['terms'] = [
      '#type' => 'radios',
      '#title' => $this->t('Export tags (keywords)'),
      '#required' => TRUE,
      '#options' => [$this->t('No'), $this->t('Yes')],
      '#description' => $this->t('Make sure that you really want this before saying "Yes" (only DK-ABM).'),
      '#default_value' => $config->get('terms') ?: 0,
    ];

    $form['format'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Where to export'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE
    ];

    $form['format']['to_danbib_ftp'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Which formats should be upload via ftp to Danbib'),
      '#required' => FALSE,
      '#options' => $this->formats(),
      '#default_value' => $config->get('to_danbib_ftp') ?: ['abm'],
    ];

    $form['format']['to_brond_ftp'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Which formats should be upload via ftp to Brønden'),
      '#required' => FALSE,
      '#options' => $this->formats(),
      '#default_value' => $config->get('to_brond_ftp') ?: ['abm'],
    ];

    $form['format']['static_files'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Which formats should be stored as static file(s) on the server (Always full export)'),
      '#required' => FALSE,
      '#options' => $this->formats(),
      '#default_value' => $config->get('static_files') ?: ['ws'],
    ];

    $form['format']['static_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Where should the static files be stored'),
      '#required' => FALSE,
      '#size' => 40,
      '#default_value' => $config->get('static_location') ?: 'export/',
    ];

    $form['format']['clean_up'] = [
      '#title' => $this->t('Remove files'),
      '#type' => 'checkboxes',
      '#description' => $this->t('Remove temporary local files'),
      '#options' => [1 => $this->t('Clean up')],
      '#default_value' => $config->get('clean_up') ?: [1],
    ];

    $form['how'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('How to export'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE
    ];

    $form['how']['library_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Library identification'),
      '#description' => $this->t('Used in the DBC "trans" file'),
      '#required' => TRUE,
      '#size' => 6,
      '#default_value' => $config->get('library_id') ?: 150005,
    ];

    $form['how']['ftp_server'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Where to upload files (FTP server)'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $config->get('ftp_server') ?: 'ftp.dbc.dk',
    ];

    $form['how']['ftp_user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $config->get('ftp_user') ?: 'ftp',
    ];

    $form['how']['ftp_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $config->get('ftp_password') ?: 'ftp',
    ];

    $form['when_what'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('When to export'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE
    ];

    $form['when_what']['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Export method'),
      '#required' => TRUE,
      '#options' => [
        'full' => $this->t('Full'),
        'inc' => $this->t('Incremental')
      ],
      '#description' => $this->t('The type of export to preform (Only full is supported).'),
      '#default_value' => $config->get('type') ?: 'full',
    ];

    $form['when_what']['time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('When to export'),
      '#date_date_element' => 'none',
      '#required' => TRUE,
      '#description' => $this->t('The export will be executed the first time cron is executed after midnight and before selected time.'),
      '#default_value' => DrupalDateTime::createFromFormat('Y-m-d H:i:s', $config->get('time') ?: '1970-01-01 00:00:00'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config('lit_xml.settings')
      // Set the submitted configuration setting.
      ->set('types', $form_state->getValue('types'))
      ->set('terms', $form_state->getValue('terms'))
      ->set('to_danbib_ftp', $form_state->getValue('to_danbib_ftp'))
      ->set('to_brond_ftp', $form_state->getValue('to_brond_ftp'))
      ->set('static_files', $form_state->getValue('static_files'))
      ->set('static_location', $form_state->getValue('static_location'))
      ->set('clean_up', $form_state->getValue('clean_up'))
      ->set('library_id', $form_state->getValue('library_id'))
      ->set('ftp_server', $form_state->getValue('ftp_server'))
      ->set('ftp_user', $form_state->getValue('ftp_user'))
      ->set('ftp_password', $form_state->getValue('ftp_password'))
      ->set('type', $form_state->getValue('type'))
      ->set('time', $form_state->getValue('time')->format('Y-m-d H:i:s'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * @return array
   */
  protected function types() {
    $types = NodeType::loadMultiple(self::$types);

    $result = [];
    foreach ($types as $type) {
      $result[$type->id()] = $type->label();
    }

    return $result;
  }

  /**
   * @return array
   */
  protected function formats() {
    return [
      'abm' => $this->t('DK-ABM (meta-data)'),
      'docbook' => $this->t('DocBook (full text)'),
      'ws' => $this->t('Web-service (Legacy)')
    ];
  }

}
