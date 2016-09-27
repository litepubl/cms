/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
 */

(function($, litepubl, window) {
  'use strict';

  litepubl.Filemanprops = Class.extend({
    item: false,
    holder: false,

    init: function(item, onclose, onset) {
      this.item = item;
      this.onclose = onclose || $.noop;
      this.onset = onset || $.noop;

      var self = this;
      var lng = lang.posteditor;
      $.litedialog({
        title: lang.posteditor.property,
        html: litepubl.tml.getedit(lng.title, "fileprop-title", item.title) +
          litepubl.tml.getedit(lng.description, "fileprop-description", item.description) +
          litepubl.tml.getedit(lng.keywords, "fileprop-keywords", item.keywords),

        open: function(holder) {
          self.holder = holder;
          $("input[name='fileprop-title']", holder).focus();
        },

        close: function() {
          self.holder = false;
          self.onclose();
        },

        buttons: [{
            title: "Ok",
            click: function() {
              var holder = self.holder;
              var values = {
                title: $.trim($("input[name='fileprop-title']", holder).val()),
                description: $.trim($("input[name='fileprop-description']", holder).val()),
                keywords: $.trim($("input[name='fileprop-keywords']", holder).val())
              };

              self.setprops(values);
              $.closedialog();
            }
          },
          $.get_cancel_button()
        ]
      });
    },

    setprops: function(values) {
      $.extend(this.item, values);
      values.idfile = this.item.id;

      return $.jsonrpc({
        method: "files_setprops",
        params: values,
        callback: this.onset,
        error: function(message, code) {
          $.errorbox(message);
        }
      });
    }

  }); //fileman

}(jQuery, litepubl, window));