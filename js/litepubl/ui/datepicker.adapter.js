/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */

(function($, document, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Datepicker = Class.extend({
    format: "dd.mm.yy",
    //url: '/js/jquery/ui/datepicker.min.js',
    url: '/js/ui-datepicker/jquery-ui.min.js',
    //langurl:'/js/jquery/ui/datepicker-%%lang%%.min.js',
    langurl: '/js/ui-datepicker/datepicker-%%lang%%.js',
    cssurl: '/js/ui-datepicker/jquery-ui.min.css',
    script: false,

    ready: function(callback) {
      if ("datepicker" in $.fn) {
        return callback();
      }

      if (this.script) {
        return this.script.done(callback);
      }

      if (this.cssurl) {
        $.load_css(ltoptions.files + this.cssurl);
      }

      var self = this;
      this.script = $.load_script(ltoptions.files + this.url, function() {
        if (ltoptions.lang == 'en') {
          if ($.isFunction(callback)) callback();
        } else {
          var langurl = self.langurl.replace('%%lang%%', ltoptions.lang);
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