/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  'use strict';
  //method to override
  litepubl.init_fileman = function(options) {
    return new litepubl.Fileman(options);
  };
  
  litepubl.Fileman = Class.extend({
    items: false,
    curr: false,
    indialog: false,
    holder: false,
    
    init: function(options) {
      try {
      this.items = {};
        this.curr = [],
        options = $.extend({
          holder: '#posteditor-filelist',
          pages: 0,
          items: false
        }, options);
        
        this.init_templates();
        this.holder = $(options.holder);
        var self = this;
        //$(holder).html(this.tml.tabs);
        var tabs = $("#posteditor-files-tabs", this.holder);
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
        
        this.holder.closest('form').submit(function() {
          $("input[name='files']", self.holder).val(self.curr.join(','));
        });
        
        this.init_upload();
        if (options.items) {
          this.set_uploaded(options.pages, options.items);
        } else {
          this.load_current_files();
        }
    } catch(e) {erralert(e);}
    },
    
    init_templates: function() {
      this.tml = litepubl.tml.fileman;
      $.replacetml(litepubl.tml.fileman, {
        lang: lang.posteditor,
        iconurl:  ltoptions.files + "/js/litepublisher/icons/"
      });
    },
    
    init_upload: function() {
      this.uploader = new litepubl.Uploader();
      this.uploader.onupload.add($.proxy(this.uploaded, this));
    },
    
    load_current_files: function() {
      var self = this;
      $.jsonrpc({
        type: 'get',
        method: "files_getpost",
      params: {idpost: ltoptions.idpost},
        callback: function (r) {
          try {
            self.set_uploaded(r.count, r.files);
        } catch(e) {erralert(e);}
        },
        
        error: function(message, code) {
          $.messagebox(lang.dialog.error, message);
        }
      });
    },
    
    set_uploaded: function(tabscount, items) {
      /*
      if ("fileperm" in r) {
        $("#posteditor-fileperms", this.uploader.holder).removeClass("hidden").html(r.fileperm);
      }
      */
      this.set_tabs_count(tabscount);
      for (var i in items) {
        var item = items[i];
        this.items[item.id] = item;
        if (parseInt(item.parent) == 0) this.curr.push(item.id);
      }
      
      this.setpage("#current-files", items);
      //to assign events
    this.setpage("#new-files", {});
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
      
      this.setborders(panel);
      
      var self = this;
      panel.on("click.toolbar", ".file-toolbar a", function() {
        var holder = $(this).closest(".file-item");
        var idfile = holder.data("idfile");
        
        switch($(this).attr("class")) {
          case "add-toolbutton":
          self.add(idfile);
          break;
          
          case "delete-toolbutton":
          self.del(idfile, holder);
          break;
          
          case "property-toolbutton":
          self.editprops(idfile, holder);
          break;
        }
        
        return false;
      });
      
      panel.on("click.image", "a.file-image", function() {
        self.openimage($(this));
        return false;
      });
      
    },
    
    openimage: function(link) {
      $.prettyPhoto.open(link.attr("href"), link.attr("title"), $("img", link).attr("alt"));
    },
    
    get_fileitem: function(id) {
      var item =this.items[id];
      item.link = ltoptions.files + "/files/" + item.filename;
      item.previewlink = '';
      var type = (item["media"] in this.tml) ? item["media"] : "file";
      
      if ((parseInt(item["preview"]) != 0) &&(item.preview in this.items)) item.previewlink = ltoptions.files + "/files/" + this.items[item["preview"]]["filename"];
      var html = $.simpletml(this.tml.item, {
        id: item["id"],
        content: $.simpletml(this.tml[type], item)
      });
      
      return $(html).data("idfile", id);
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
    
    joinitems: function(files) {
      for (var id in files) {
        this.items[id] = files[id];
      }
    },
    
    uploaded: function(r) {
      try {
        /*
        r = {
          id: int idfile,
          item: array fileitem,
          preview: array fileitem optimal
        }
        */
        
        var idfile = r.id;
        this.curr.push(idfile);
        this.items[idfile] = r.item;
        if (parseInt(r.item.preview) != 0) this.items[r.preview.id] = r.preview;
        
        $("#current-files .file-items").append(this.get_fileitem(idfile));
        $("#new-files .file-items").append(this.get_fileitem(idfile));
    } catch(e) {erralert(e);}
    },
    
    setborders: function(uipanel) {
      var all = $(".file-item", uipanel);
      if (all.length == 0) return;
      all.removeClass("border-left");
      var firstpos = $(".file-item:first", uipanel).position();
      all.each(function() {
        var self = $(this);
        var pos = self.position();
        if (pos.left == firstpos.left) self.addClass("border-left");
      });
    },
    
    add: function(idfile) {
      if ($.inArray(idfile, this.curr) >= 0) return;
      this.curr.push(idfile);
      
      this.setborders($("#current-files .file-items").append(this.get_fileitem(idfile)));
    },
    
    del: function(idfile, holder) {
      var i = $.inArray(idfile, this.curr);
      if (i < 0) {
        idfile = parseInt(idfile);
        var i = $.inArray(idfile, this.curr);
        if (i < 0) return;
      }
      
      this.curr.splice(i, 1);
      var parent = holder.parent();
      holder.remove();
      this.setborders(parent);
    },
    
    editprops: function(idfile, owner) {
      if (this.indialog) return false;
      this.indialog = true;
      var fileitem = this.items[idfile];
      var self = this;
      
      $.litedialog({
        title: lang.posteditor.property,
        html: this.tml.fileprops,
        open: function(holder) {
          $("input[name='fileprop-title']", holder).val(fileitem.title);
          $("input[name='fileprop-description']", holder).val(fileitem.description);
          $("input[name='fileprop-keywords']", holder).val(fileitem.keywords);
        },
        
        buttons: [
        {
          title: "Ok",
          click: function() {
            var holder = $(".pp_inline");
            var values = {
              title: $.trim($("input[name='fileprop-title']", holder).val()),
              description: $.trim($("input[name='fileprop-description']", holder).val()),
              keywords: $.trim($("input[name='fileprop-keywords']", holder).val())
            };
            
            $.closedialog();
            self.setprops(idfile, values, owner);
          }
        },
        {
          title: lang.dialog.cancel,
          click: function() {
            $.closedialog();
            self.indialog = false;
          }
        }
        ]
      } );
    },
    
    setprops: function(idfile, values, holder) {
      $.extend(this.items[idfile], values);
      values.idfile = idfile;
      var self = this;
      return $.jsonrpc({
        method: "files_setprops",
        params: values,
        callback: function(r) {
          self.items[r.item["id"]] = r.item;
          //need to update infos but we cant find all files
          if (!!holder) holder.replaceWith(self.get_fileitem(idfile));
          self.indialog = false;
        },
        
        error: function(message, code) {
          self.indialog = false;
          $.messagebox(lang.dialog.error, message);
        }
      });
    }
    
  });//fileman
}(jQuery, litepubl, window));