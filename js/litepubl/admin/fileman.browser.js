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

  litepubl.Filemanbrowser = Class.extend({
    perpage: 10,
    pages: false,
    fileman: false,
    css_small_height: '#posteditor-files-tabs.file-items {' +
      'height:132px' +
      '}',

    init: function(fileman) {
      this.fileman = fileman;
      this.pages = {};
      this.open();
    },

    add: function(id) {
      this.fileman.add(id);
    },

    open: function() {
      var winwidth = $(window).width();
      var winheight = $(window).height();
      var dialog_height = 495;

      var self = this;
      $.litedialog({
        title: lang.posteditor.selectfiles,
        html: this.get_html(),
        width: Math.min(720, winwidth - 120),
        height: Math.min(dialog_height, winheight - 60),
        css: winheight - 60 < dialog_height ? this.css_small_height : '',
        open: function(holder) {
          holder.on("click.addfile", ".file-item", function() {
            var item = $(this);
            if (!item.hasClass("file-added")) {
              item.addClass("file-added");
              self.add(item.attr("data-idfile"));
            }

            return false;
          });

          // load first page because ui tabs not fire beforeActivate
          self.loadpage(holder.find(".file-items:first"), "1");

          var tabs = $("#posteditor-files-tabs", holder);
          litepubl.tabs(tabs, {
            before: function(tabpanel) {
              var panel = tabpanel.children();
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
      });
    },

    get_html: function() {
      var tml_tab = litepubl.tml.fileman.tab;
      var tml = litepubl.tabs.gettml();
      var pages = Math.ceil(this.fileman.count / this.perpage);
      var head = "";
      var body = "";

      for (var page = 1; page <= pages; page++) {
        head += $.parsetml(tml.head, {
          id: ++litepubl.guid,
          title: page
        });

        body += $.parsetml(tml.tab, {
          id: litepubl.guid,
          content: $.parsetml(tml_tab, {
            page: page
          })
        });
      }

      return $.parsetml(tml.tabs, {
        head: head,
        tab: body
      });
    },

    loadpage: function(panel, page) {
      if (page in this.pages) {
        panel.attr("data-status", "loaded");
        panel.append(this.getpage(page));
      } else {
        panel.attr("data-status", "loading");

        var self = this;
        $.jsonrpc({
          type: 'get',
          method: "files_getpage",
          params: {
            page: page - 1,
            perpage: this.perpage
          },
          callback: function(r) {
            self.addpage(page, r.files);
            panel.attr("data-status", "loaded");
            panel.append(self.getpage(page));
          },

          error: function(message, code) {
            panel.attr("data-status", "error");
            panel.append('<p>' + message + '</p>');
          }
        });
      }
    },

    addpage: function(page, items) {
      var list = this.pages[page] = [];
      var fileitems = this.fileman.items;

      for (var i in items) {
        var item = items[i];
        fileitems[item.id] = item;
        if (!parseInt(item.parent)) {
          list.push(item.id);
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
        if ($.inArray(id, fileman.loaded) < 0) {
          result += fileman.get_fileitem(id);
        }
      }

      fileman.tml.toolbar = toolbar;
      return result;
    }

  }); //fileman

}(jQuery, litepubl, window));