/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  'use strict';

  //factory to create file manager
  litepubl.init_fileman = function(options) {
    return new litepubl.Fileman(options);
  };
  
  litepubl.Fileman = Class.extend({
    loaded: false, //[id, ...] current attached files to post
    items: false, // {} all files
    indialog: false,
    holder: false,
    
    init: function(options) {
      this.loaded = [],
    this.items = {};

      options = $.extend({
        holder: '#posteditor-filelist',
        pages: 0,
        items: false
      }, options);
      
      this.tml = litepubl.tml.fileman;
      $.replacetml(this.tml, {
        lang: lang.posteditor
      });

        var self = this;
        this.holder = $(options.holder);
        this.holder.closest('form').submit(function() {
          $("input[name='files']", self.holder).val(self.loaded.join(','));
        });
        
try {
        this.init_uploader();
        if (options.items) {
          this.set_uploaded(options.items);
        } else {
          this.files_getpost();
        }
    } catch(e) {erralert(e);}
    },

    files_getpost: function() {
      var self = this;
      $.jsonrpc({
        type: 'get',
        method: "files_getpost",
      params: {idpost: ltoptions.idpost},
        callback: function (r) {
          try {
            self.set_uploaded(r.files);
        } catch(e) {erralert(e);}
        },
        
        error: function(message, code) {
          $.errorbox(message);
        }
      });
    },

    init_uploader: function() {
      this.uploader = new litepubl.Uploader();
      this.uploader.onupload.add($.proxy(this.uploaded, this));
    },
    
    set_uploaded: function(items) {
      for (var i in items) {
        var item = items[i];
        this.items[item.id] = item;
        if (!parseInt(item.parent) ) this.loaded.push(item.id);
      }
      
      this.setpage("#current-files", items);
    this.setpage("#new-files", {});
    },
    
    setpage: function(uipanel, files) {
      var panel = $(".file-items", uipanel);
      for (var id in files) {
        if (parseInt(files[id]['parent']) != 0) continue;
        panel.append(this.get_fileitem(id));
      }
      
      var self = this;
      panel.on("click.toolbar", ".file-toolbar > a, .file-toolbar > button", function() {
        var button = $(this);
        var holder = button.closest(".file-item");
        var idfile = holder.data("idfile");
        
        if (button.hasClass("add-toolbutton")) {
          self.add(idfile);
        } else if (button.hasClass("delete-toolbutton")) {
          self.del(idfile, holder);
        } else if (button.hasClass("property-toolbutton")) {
          self.editprops(idfile, holder);
        }
        
        return false;
      });
      
      panel.on("click.image", "a.file-image", function() {
        self.openimage($(this));
        return false;
      });
      
    },
openimage: function(link) {
litepubl.linkimage(link);
},
    
    get_fileitem: function(id) {
      var item =this.items[id];
      item.link = ltoptions.files + "/files/" + item.filename;
      item.previewlink = '';
      var type = (item["media"] in this.tml) ? item["media"] : "file";
      
      if (parseInt(item.preview) &&(item.preview in this.items)) {
item.previewlink = ltoptions.files + "/files/" + this.items[item.preview]["filename"];
}

      var html = $.simpletml(this.tml.item, {
        id: item.id,
        content: $.simpletml(this.tml[type], item)
      });
      
      return $(html).data("idfile", id);
    },
    
    joinitems: function(files) {
      for (var id in files) {
        this.items[id] = files[id];
      }
    },

        /*
        r = {
          id: int idfile,
          item: array fileitem,
          preview: array fileitem optimal
        }
        */
    uploaded: function(r) {
      try {        
        var idfile = r.id;
        this.loaded.push(idfile);
        this.items[idfile] = r.item;
        if (parseInt(r.item.preview)) this.items[r.preview.id] = r.preview;
        
        $("#current-files .file-items").append(this.get_fileitem(idfile));
        $("#new-files .file-items").append(this.get_fileitem(idfile));
    } catch(e) {erralert(e);}
    },
    
    add: function(idfile) {
      if ($.inArray(idfile, this.loaded) < 0) {
        this.loaded.push(idfile);
      }
    },
    
    del: function(idfile, holder) {
      var i = $.inArray(idfile, this.loaded);
      if (i < 0) {
        idfile = parseInt(idfile);
        var i = $.inArray(idfile, this.loaded);
        if (i < 0) return;
      }
      
      this.loaded.splice(i, 1);
      holder.remove();
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