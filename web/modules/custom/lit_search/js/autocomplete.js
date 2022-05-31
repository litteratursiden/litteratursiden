(function ($, Drupal, drupalSettings) {
  'use strict';

  let length = 3;
  let lastString = '';

  /**
   * Helper function to delay the input and search execution.
   *
   * @type {(function(*, *): void)|*}
   * @private
   */
  let _delay = function _delay() {
    let timer = 0;
    return function (callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  }();

  Drupal.behaviors.lit_search_autocomplete = {
    attach: function attach(context, settings) {
      $('.lit-search-autocomplete-field').on('keyup', function () {
        let el = $(this);
        let totalEl = el.parent().next('.lit-search-autocomplete-total');
        let resultsEl = totalEl.parent().next('.lit-search-autocomplete-results');

        console.log(el.val(),'->',lastString);
        if (el.val() === lastString) {
          return;
        }
        lastString = el.val();

        el.parent().find('.loader').remove();
        el.parent().append('<div class="loader"></div>');

        _delay(function () {
          if (el.val() && el.val().length >= length) {
            jQuery.ajax({
              dataType: "json",
              url: drupalSettings.path.baseUrl + "autocomplete",
              data: { q: el.val() },
              success: function success(result) {
                //Remove all other instances
                totalEl.html('');
                resultsEl.html('');
                el.parent().find('.loader').remove();
                el.parent().addClass('active');

                totalEl.html('(' + result.total + ')');
                resultsEl.html('<div class="container">' + result.data + '</div>');

                $(document).click(function(event) {
                  if ( !$(event.target).parents().hasClass('lit-search-autocomplete-results')) {
                    $(document).find('.lit-search-autocomplete-total').html('');
                    $(document).find('.lit-search-autocomplete-results').html('');
                    el.parent().removeClass('active');
                    lastString = '';
                  }
                });
                $('body').on('click', function (e) {
                  if (e.target.id === 'edit-search-results' || e.target.id === 'edit-search-results--2' ) {
                    return false
                  }
                });
              }
            });
          } else {
            el.parent().removeClass('active');
            el.parent().find('.loader').remove();
            totalEl.html('');
            resultsEl.html('');
            $(document).find('#bodyOverlay').remove();
            lastString = '';
          }
        }, 300);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
