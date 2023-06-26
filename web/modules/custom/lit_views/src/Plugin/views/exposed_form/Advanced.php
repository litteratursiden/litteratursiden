<?php

namespace Drupal\lit_views\Plugin\views\exposed_form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\exposed_form\Basic;

/**
 * Exposed form plugin that provides an advanced exposed form.
 *
 * @ingroup views_exposed_form_plugins
 *
 * @ViewsExposedForm(
 *   id = "lit_views_advanced",
 *   title = @Translation("Advanced"),
 *   help = @Translation("Advanced exposed form")
 * )
 */
class Advanced extends Basic {

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['expose_filter_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Exposed filters display type'),
      '#options' => [
        'default' => $this->t('Default'),
        'hidden' => $this->t('Hidden'),
      ],
      '#default_value' => $this->options['expose_filter_display'],
    ];

    $form['expose_sort_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Exposed sorts display type'),
      '#options' => [
        'default' => $this->t('Default'),
        'hidden' => $this->t('Hidden'),
        'links' => $this->t('Links'),
      ],
      '#default_value' => $this->options['expose_sort_display'],
    ];

    $form['exposed_sorts_label']['#required'] = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function renderExposedForm($block = FALSE) {
    // Deal with any exposed filters we may have, before building.
    $form_state = (new FormState())
      ->setStorage([
        'view' => $this->view,
        'display' => &$this->view->display_handler->display,
        'rerender' => TRUE,
      ])
      ->setMethod('get')
      ->setAlwaysProcess()
      ->disableRedirect();

    // Some types of displays (eg. attachments) may wish to use the exposed
    // filters of their parent displays instead of showing an additional
    // exposed filter form for the attachment as well as that for the parent.
    if (!$this->view->display_handler->displaysExposed() || (!$block && $this->view->display_handler->getOption('exposed_block'))) {
      $form_state->set('rerender', NULL);
    }

    if (!empty($this->ajax)) {
      $form_state->set('ajax', TRUE);
    }

    $form = \Drupal::formBuilder()->buildForm('\Drupal\views\Form\ViewsExposedForm', $form_state);
    $errors = $form_state->getErrors();

    // If the exposed form had errors, do not build the view.
    if (!empty($errors)) {
      $this->view->build_info['abort'] = TRUE;
    }

    if (!$this->view->display_handler->displaysExposed() || (!$block && $this->view->display_handler->getOption('exposed_block'))) {
      return [];
    }
    else {
      return $form;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function exposedFormAlter(&$form, FormStateInterface $form_state) {
    $view = $this->view;

    $view->exposed_data = $view->exposed_raw_input += $this->view->getExposedInput();

    if (!empty($this->options['submit_button'])) {
      $form['actions']['submit']['#value'] = $this->options['submit_button'];
    }

    // Check if there is exposed sorts for this view.
    $exposed_sorts = [];
    foreach ($this->view->sort as $id => $handler) {
      if ($handler->canExpose() && $handler->isExposed()) {
        $exposed_sorts[$id] = Html::escape($handler->options['expose']['label']);
      }
    }

    if (count($exposed_sorts) && $this->options['expose_sort_display'] != 'hidden') {
      if ($this->options['expose_sort_display'] == 'links') {
        $this->exposedSortsAsLinks($exposed_sorts, $form, $form_state);
      }
      else {
        $this->exposedSortsAsDefault($exposed_sorts, $form, $form_state);
      }
    }

    $form['actions']['#weight'] = 10;

    if (!empty($this->options['reset_button'])) {
      $form['actions']['reset'] = [
        '#value' => $this->options['reset_button_label'],
        '#type' => 'submit',
        '#weight' => 10,
      ];

      // Get an array of exposed filters, keyed by identifier option.
      $exposed_filters = [];
      foreach ($this->view->filter as $id => $handler) {
        if ($handler->canExpose() && $handler->isExposed() && !empty($handler->options['expose']['identifier'])) {
          $exposed_filters[$handler->options['expose']['identifier']] = $id;
        }
      }
      $all_exposed = array_merge($exposed_sorts, $exposed_filters);

      // Set the access to FALSE if there is no exposed input.
      if (!array_intersect_key($all_exposed, $this->view->getExposedInput())) {
        $form['actions']['reset']['#access'] = FALSE;
      }
    }

    $pager = $this->view->display_handler->getPlugin('pager');
    if ($pager) {
      $pager->exposedFormAlter($form, $form_state);
      $form_state->set('pager_plugin', $pager);
    }
  }

  /**
   * Render exposed sorts as links.
   *
   * @param $exposed_sorts
   * @param $form
   * @param $form_state
   */
  private function exposedSortsAsLinks($exposed_sorts, &$form, &$form_state) {
    $user_input = $form_state->getUserInput();

    $form['sort_links'] = [
      '#type' => 'item',
      '#title' => $this->options['exposed_sorts_label'],
      '#weight' => 100,
    ];

    $first_sort = reset($this->view->sort);
    $default_sort_id = $first_sort->options['id'];

    foreach ($exposed_sorts as $name => $exposed_sort) {
      $default_sort = $this->view->sort[$name]->options['order'];

      $query = $user_input;
      $query['sort_by'] = $name;
      $query['sort_order'] = $default_sort == 'ASC' ? 'DESC' : 'ASC';
      $classes = [];

      if (isset($user_input['sort_by']) && $user_input['sort_by'] == $name) {
        if (isset($user_input['sort_order'])) {
          $query['sort_order'] = $user_input['sort_order'] == 'ASC' ? 'DESC' : 'ASC';
        }

        $classes[] = 'active';
      }
      elseif (!isset($user_input['sort_by']) && $default_sort_id == $name) {
        $classes[] = 'active';
      }
      else {
        $query['sort_order'] = $default_sort;
      }


      $classes[] = strtolower($query['sort_order']);

      $form['sort_links'][$name] = [
        '#title' => $exposed_sort,
        '#type' => 'link',
        '#url' => Url::fromRoute('view.search.search', [], ['query' => $query]),
        '#attributes' => [
          'class' => $classes,
        ],
      ];
    }

    if (!isset($user_input['sort_by'])) {
      $keys = array_keys($exposed_sorts);
      $user_input['sort_by'] = array_shift($keys);
      $form_state->setUserInput($user_input);
    }
  }

  /**
   * Render exposed sorts as default fields.
   *
   * @param $exposed_sorts
   * @param $form
   * @param $form_state
   */
  private function exposedSortsAsDefault($exposed_sorts, &$form, &$form_state) {
    $form['sort_by'] = [
      '#type' => 'select',
      '#options' => $exposed_sorts,
      '#title' => $this->options['exposed_sorts_label'],
    ];
    $sort_order = [
      'ASC' => $this->options['sort_asc_label'],
      'DESC' => $this->options['sort_desc_label'],
    ];
    $user_input = $form_state->getUserInput();
    if (isset($user_input['sort_by']) && isset($this->view->sort[$user_input['sort_by']])) {
      $default_sort_order = $this->view->sort[$user_input['sort_by']]->options['order'];
    }
    else {
      $first_sort = reset($this->view->sort);
      $default_sort_order = $first_sort->options['order'];
    }

    if (!isset($user_input['sort_by'])) {
      $keys = array_keys($exposed_sorts);
      $user_input['sort_by'] = array_shift($keys);
      $form_state->setUserInput($user_input);
    }

    if ($this->options['expose_sort_order']) {
      $form['sort_order'] = [
        '#type' => 'select',
        '#options' => $sort_order,
        '#title' => $this->t('Order', [], ['context' => 'Sort order']),
        '#default_value' => $default_sort_order,
      ];
    }
    $form['submit']['#weight'] = 10;
  }

}
