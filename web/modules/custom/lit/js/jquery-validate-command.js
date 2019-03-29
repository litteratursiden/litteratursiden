(function ($, Drupal, settings) {

  "use strict";

  /**
   * Shows errors of form elements.
   * It uses function showErrors() of jquery validation plugin.
   *
   * @param {Drupal.Ajax} [ajax]
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} [response]
   *   The response from the Ajax request.
   * @param {string} response.selector
   *   A jQuery selector string for the form or element inside the form for
   *     which errors should be shown. If null is given the first form on the
   *     page will be taken. The jquery validation should be ran on the
   *     selector's form. ClientSide Validation JQuery module provides this for
   *     all forms out of the box.
   * @param response.errors
   *   One or more key/value pairs of input names and messages.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   *
   * @see https://jqueryvalidation.org/Validator.showErrors/
   */
  Drupal.AjaxCommands.prototype.showFormErrors = function (ajax, response, status) {
    var form = $(response.selector);
    if (!form.is('form')) {
      form = response.selector ? form.closest('form') : $(document).find('form');
    }

    if (!form.is('form')) {
      console.error('Wasn\'t able to find a form for showing errors.');
      return;
    }

    if (!$.isEmptyObject(response.errors)) {
      setTimeout(function () {
        var validator = form.data('validator');
        validator.showErrors(response.errors);

        // Focusing first error element.
        var first_element_name = Object.keys(response.errors)[0];
        form.find('[name="' + first_element_name + '"]').focus();
      }, 100);
    }
  };

})(jQuery, Drupal, drupalSettings);
