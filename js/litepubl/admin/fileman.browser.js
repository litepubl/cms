/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  'use strict';

  litepubl.Filemanbrowser = Class.extend({
    holder: false,
    
    init: function(fileman) {
        var self = this;
$.litedialog({
        title: lang.posteditor.property,
        html: html,
        open: function(holder) {
self.holder = holder;
        var tabs = $("#posteditor-files-tabs", holder);
        tabs.tabs({
          hide: true,
          show: true,
          beforeLoad: litepubl.uibefore,
          beforeActivate: function(event, ui) {
            if ("empty" == $(ui.newPanel).data("files")) {
              self.loadpage(ui.newPanel, $(ui.newPanel).data("page"));
            }
          }
        });
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

    set_tabs_count: function(count) {
      if (count < 1) return;
      var tabs = $("#posteditor-files-tabs", this.holder);
      var tabhead = $(".ui-tabs-nav", tabs);
      for (var i =1; i <= count; i++) {
        $(this.tml.tab.replace('%%index%%', i)).appendTo(tabs).data("page", i).data("files", "empty");
        $(this.tml.tabli.replace(/%%index%%/gim, i)).appendTo(tabhead);
      }
      tabs.tabs( "refresh" );
    },
    
    setpage: function(uipanel, files) {
      var panel = $(".file-items", uipanel);
      for (var id in files) {
        if (parseInt(files[id]['parent']) != 0) continue;
        panel.append(this.get_fileitem(id));
      }
      
},

    
    loadpage: function(uipanel, page) {
      var self = this;
      $(uipanel).data("files", "loading");
      $.jsonrpc({
        type: 'get',
        method: "files_getpage",
      params: {page: page - 1},
        callback: function(r) {
          self.joinitems(r.files);
          self.setpage(uipanel, r.files);
        },
        
        error:  function(message, code) {
          $.messagebox(lang.dialog.error, message);
        }
      });
    },
  });//fileman
}(jQuery, litepubl, window));