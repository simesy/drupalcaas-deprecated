"use strict";

(function($, Drupal) {
  Drupal.behaviors.tableResponsive = {
    attach: function(context, settings) {
      // Table responsive
      var $table = $("table");
      if ($table.length && !$table.parent().hasClass("table-responsive")) {
        $table
          .not($table.find("table"))
          .wrap('<div class="table-responsive"></div>');
      }
    },
  };
})(jQuery, Drupal);
