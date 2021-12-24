/**
 * Drupal behaviors for openy_rose
 */
(function ($) {
  "use strict";

  /**
   * Set css class 'primary-menu-minimize' for body element after primary menu affix activated on scroll
   * This function is needed for Virtual Y top menu to follow primary menu height change
   */
  Drupal.behaviors.OpenyRoseAffixClassForPrimaryMenu = {
    attach: function (context, settings) {
      let originalAddClassMethod    = jQuery.fn.addClass;
      let originalRemoveClassMethod = jQuery.fn.removeClass;
      let trackClassChange = $(".top-navs", context).attr('class')

      jQuery.fn.addClass            = function () {
        let result = originalAddClassMethod.apply(this, arguments);
        jQuery(this, context).trigger('classChanged');
        return result;
      }
      jQuery.fn.removeClass         = function () {
        let result = originalRemoveClassMethod.apply(this, arguments);
        jQuery(this, context).trigger('classChanged');
        return result;
      }

      $(".top-navs", context).on(
        "classChanged", function () {
          let newClasses = $(this, context).attr('class')

          // Process only if classes are different. Reduce a number of processing for admin user.
          if (newClasses !== trackClassChange) {
            if ($('.top-navs', context).hasClass('affix')
              && !$('body', context).hasClass('primary-menu-minimize')) {
              $('body', context).addClass('primary-menu-minimize');
            } else if ($('body', context).hasClass('primary-menu-minimize')) {
              $('body', context).removeClass('primary-menu-minimize');
            }
            trackClassChange = newClasses;
          }
        });
    }
  };
})(jQuery);
