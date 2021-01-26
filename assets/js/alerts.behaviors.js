/**
 * Drupal behaviors to track that OpenY Alerts are present on the page.
 */
(function ($) {
  "use strict";

  /**
   * Set 'alerts' class for body elements if there is OpenY Alerts are present on the page.
   */
  Drupal.behaviors.virtualYAlerts = {
    attach: function (context, settings) {
      var alertsAppContainer = function findAlertsAppContainer() {
        var container = null;
        var customPlaceholder = document.getElementById('openy_alerts_header_placeholder');
        if (customPlaceholder) {
          container = customPlaceholder.parentNode;
        }

        var defaultPlaceholder = document.getElementsByTagName('header')[0];
        if (defaultPlaceholder) {
          container = defaultPlaceholder.parentNode;
        }

        return container;
      }();

      var alertsAppCreatedCallback = function(mutationsList, observer) {
        for (var i=0; i<mutationsList.length; i++) {
          if (mutationsList[i].target.classList.contains('slick-track')) {
            if ($('#openy_alerts_app_header .slick-slide').length > 0) {
              $('body').addClass('alerts');
            } else {
              $('body').removeClass('alerts');
            }
          }
        }
      }

      if (alertsAppContainer) {
        var alertsAppCreatedObserver = new MutationObserver(alertsAppCreatedCallback);
        var observeConfig = { childList: true, subtree: true };
        alertsAppCreatedObserver.observe(alertsAppContainer, observeConfig);
      } else {
        console.log('Alerts container not found.');
      }
    }
  };
})(jQuery);
