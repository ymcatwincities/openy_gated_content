
(function ($, Drupal) {
 Drupal.behaviors.openy_gc_log_datepicker = {
   attach: function (context) {
     $('#edit-created-min, #edit-created-max, #edit-changed-from, #edit-changed-to').datepicker({
       dateFormat: "yy-mm-dd"
     });
   }
 }
})(jQuery, Drupal);
