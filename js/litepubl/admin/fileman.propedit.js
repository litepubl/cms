/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  'use strict';

  litepubl.Filemanprops = Class.extend({
item: false,
holder: false,

    init: function(item, onclose, onset) {
this.item = item;
this.onclose = onclose || $.noop;
this.onset =  onset || $.noop;

var self = this;
$.litedialog({
        title: lang.posteditor.property,
        html: litepubl.tml.fileman.fileprops,
        open: function(holder) {
self.holder = holder;
var item = self.item;
          $("input[name='fileprop-title']", holder).val(item.title);
          $("input[name='fileprop-description']", holder).val(item.description);
          $("input[name='fileprop-keywords']", holder).val(item.keywords);
        },

close: function() {
self.holder = false;
self.onclose();
},
        
        buttons: [
        {
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
      } );
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
    
  });//fileman

}(jQuery, litepubl, window));