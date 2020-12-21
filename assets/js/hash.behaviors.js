/**
 * Drupal behaviors for that fix conflict between vue hash router and drupal.
 */
(function ($) {
  "use strict";

  /**
   * Fix console hash error.
   */
  Drupal.behaviors.virtualYHashConflictFix = {
    attach: function (context, settings) {
      // Fix console errors.
      // Uncaught Error: Syntax error, unrecognized expression: #/categories/blog
      $(window).off('hashchange.form-fragment');
      $(document).off('click.form-fragment');
    }
  };

})(jQuery);
