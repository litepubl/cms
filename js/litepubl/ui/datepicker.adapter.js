(function($, document, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Datepicker = Class.extend({
format: "dd.mm.yy",
url: '/js/jquery/ui/datepicker.min.js',
langurl:'/js/jquery/ui/datepicker-%%lang%%.min.js',
script: false,

    init: function() {
},

    ready: function(callback) {
if ("datepicker" in $.fn) {
return callback();
}

      if (this.script) {
return this.script.done(callback);
}

      var self = this;
      this.script = $.load_script(ltoptions.files + this.url, function() {
        if (ltoptions.lang == 'en') {
if ($.isFunction(callback)) callback();
} else {
var langurl = self.langurl.replace('%%lang%%', ltoptions.lang );
        self.script = $.load_script(ltoptions.files + langurl, callback);
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