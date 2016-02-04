(function($, document, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Datepicker = Class.extend({
format: "dd.mm.yy",
script: false,

    init: function() {
},

    ready: function(callback) {
      if (this.script) {
return this.script.done(callback);
}

      var self = this;
      this.script = $.load_script(ltoptions.files + '/js/jquery/ui/datepicker.min.js', function() {
        if (ltoptions.lang == 'en') {
if ($.isFunction(callback)) callback();
} else {
        self.script = $.load_script(ltoptions.files + '/js/jquery/ui/datepicker-' + ltoptions.lang + '.min.js', callback);
}
      });
    },

    datepicker: function(holder, edit) {
      $(holder).datepicker({
        altField: edit,
        altFormat: this.format,
        dateFormat: this.format,
        defaultDate: edit.val(),
        changeYear: true
      });
    }

  });
  $(document).ready(function() {
    litepubl.datepicker = new litepubl.ui.Datepicker();
  });

})(jQuery, document, litepubl);