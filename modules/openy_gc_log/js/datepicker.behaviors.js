
(function ($, Drupal) {
 Drupal.behaviors.opey_gc_log_datepicker = {
   attach: function (context) {
     $('#edit-created-min, #edit-created-max').datepicker({
       dateFormat: "yy-mm-dd"
     });
   }
 }
})(jQuery, Drupal);
