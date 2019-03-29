<?php

namespace Drupal\lit_views\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the views exposed form.
 */
class ViewsExposedForm extends \Drupal\views\Form\ViewsExposedForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Don't show the form when batch operations are in progress.
    if ($batch = batch_get() && isset($batch['current_set'])) {
      return [
        // Set the theme callback to be nothing to avoid errors in template_preprocess_views_exposed_form().
        '#theme' => '',
      ];
    }

    // Make sure that we validate because this form might be submitted
    // multiple times per page.
    $form_state->setValidationEnforced();
    /** @var \Drupal\views\ViewExecutable $view */
    $view = $form_state->get('view');
    $display = &$form_state->get('display');

    $form_state->setUserInput($view->getExposedInput());

    // Let form plugins know this is for exposed widgets.
    $form_state->set('exposed', TRUE);
    // Check if the form was already created
    if ($cache = $this->exposedFormCache->getForm($view->storage->id(), $view->current_display)) {
      return $cache;
    }

    $form['#info'] = [];

    $exposed_form_plugin = $view->display_handler->getPlugin('exposed_form');

    if ($exposed_form_plugin->options['expose_filter_display'] == 'default') {
      // Go through each handler and let it generate its exposed widget.
      foreach ($view->display_handler->handlers as $type => $value) {
        /** @var \Drupal\views\Plugin\views\ViewsHandlerInterface $handler */
        foreach ($view->$type as $id => $handler) {
          if ($handler->canExpose() && $handler->isExposed()) {
            // Grouped exposed filters have their own forms.
            // Instead of render the standard exposed form, a new Select or
            // Radio form field is rendered with the available groups.
            // When an user choose an option the selected value is split
            // into the operator and value that the item represents.
            if ($handler->isAGroup()) {
              $handler->groupForm($form, $form_state);
              $id = $handler->options['group_info']['identifier'];
            }
            else {
              $handler->buildExposedForm($form, $form_state);
            }
            if ($info = $handler->exposedInfo()) {
              $form['#info']["$type-$id"] = $info;
            }
          }
        }
      }

      $form['actions'] = [
        '#type' => 'actions'
      ];
      $form['actions']['submit'] = [
        // Prevent from showing up in \Drupal::request()->query.
        '#name' => '',
        '#type' => 'submit',
        '#value' => $this->t('Apply'),
        '#id' => Html::getUniqueId('edit-submit-' . $view->storage->id()),
      ];
    }

    $form['#action'] = $view->hasUrl() ? $view->getUrl()->toString() : Url::fromRoute('<current>')->toString();
    $form['#theme'] = $view->buildThemeFunctions('views_exposed_form');
    $form['#id'] = Html::cleanCssIdentifier('views_exposed_form-' . $view->storage->id() . '-' . $display['id']);

    /** @var \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginInterface $exposed_form_plugin */
    $exposed_form_plugin = $view->display_handler->getPlugin('exposed_form');
    $exposed_form_plugin->exposedFormAlter($form, $form_state);

    // Save the form.
    $this->exposedFormCache->setForm($view->storage->id(), $view->current_display, $form);

    return $form;
  }

}
