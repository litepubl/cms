/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  'use strict';

  litepubl.Filemanbrowser = Class.extend({
perpage: 10,
pages: false,
fileman: false,

    init: function(fileman) {
this.pages = {};
this.fileman = fileman;
this.open();
},

open: function() {
        var self = this;
$.litedialog({
        title: lang.posteditor.selectfile,
        html: this.get_html(),
        open: function(holder) {
holder.on("click.addfile", ".file-image:not(.file-added)", function() {
self.add($(this).addClass("file-added"));
return false;
});

        var tabs = $("#posteditor-files-tabs", holder);
        tabs.tabs({
          hide: true,
          show: true,
          beforeLoad: litepubl.uibefore,
          beforeActivate: function(event, ui) {
var panel = $(ui.newPanel);
            if ("empty" == panel.attr("data-status")) {
              self.loadpage(panel, panel.attr("data-page"));
            }
          }
        });
},

        buttons: [{
          title: lang.dialog.close,
          click: $.closedialog
        }]
      } );
},

    get_html: function() {
var tml = litepubl.tml.fileman;
var pages = Math.ceil(this.fileman.count / this.perpage);
var head = "";
var body = "";
      for (var i =1; i <= pages; i++) {
head +=         tml.tabhead.replace(/%%index%%/gim, i);
        body += tml.tab.replace(/%%index%%/gim, i);
}

return $.parsetml(tml.tabs, {
head: head,
body: body
});
    },
    
    loadpage: function(panel, page) {
if (page in this.pages) {
panel.attr("data-status", "loaded");
          panel.append(this.getpage(page));
return;
}

      var self = this;
panel.attr("data-status", "loading");

      $.jsonrpc({
        type: 'get',
        method: "files_getpage",
      params: {page: page - 1},
        callback: function(r) {
          self.fileman.additems(r.files);
          self.addpage(page, r.files);
panel.attr("data-status", "loaded");
          panel.append(self.getpage(page));
        },
        
        error:  function(message, code) {
panel.attr("data-status", "error");
panel.append('<p>' + message + '</p>');
        }
      });
    },

addpage: function(page, items) {
var list = this.pages[page] = [];
      for (var id in items) {
        if (!parseInt(items[id].parent)) {
list.push(id);
}
}
},

    getpage: function(page) {
var result = '';
var list = this.pages[page];
var items = this.fileman.items;
var tml = litepubl.tml.fileman.file;
      for (var i = 0, l = list.length; i < l; i++) {
result += $.parsetml(tml, items[list[i]]);
      }
      
return result;
}

  });//fileman

}(jQuery, litepubl, window));