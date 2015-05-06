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
    
    add: function(id) {
      this.fileman.add(id);
    },
    
    open: function() {
      var winwidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
      var winheight = window.innerheight || document.documentElement.clientheight || document.body.clientheight;

      var self = this;
      $.litedialog({
        title: lang.posteditor.selectfiles,
        html: this.get_html(),
        width: Math.floor(winwidth / 4 * 3),
height: Math.min(450, winheight - 60),
        open: function(holder) {
          holder.on("click.addfile", ".file-item:not(.file-added)", function() {
            self.add($(this).addClass("file-added").attr("data-idfile"));
            return false;
          });
          
// load first page because ui tabs not fire beforeActivate
                self.loadpage(holder.find(".file-items:first"), "1");

          var tabs = $("#posteditor-files-tabs", holder);
          tabs.tabs({
            hide: true,
            show: true,
            beforeLoad: litepubl.uibefore,
            beforeActivate: function(event, ui) {
              var panel = $(ui.newPanel.firstChild);
              if ("empty" == panel.attr("data-status")) {
                self.loadpage(panel, panel.attr("data-page"));
              }
            }
          });
        },

close: function() {
self.fileman.dialog = false;
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
      var fileitems = this.fileman.items;
      for (var id in items) {
        fileitems[id] = items[id];
        if (!parseInt(items[id].parent)) {
          list.push(id);
        }
      }
    },
    
    getpage: function(page) {
      var result = '';
      var list = this.pages[page];
      var fileman = this.fileman;

//save tml toolbar before and restore after generate html
var toolbar = fileman.tml.toolbar;
fileman.tml.toolbar = "";

      for (var i = 0, l = list.length; i < l; i++) {
        var id = list[i];
        if ($.inArray(id , fileman.loaded) < 0) {
          result += fileman.get_fileitem(id);
        }
      }
      
fileman.tml.toolbar = toolbar;
      return result;
    }
    
  });//fileman
  
}(jQuery, litepubl, window));