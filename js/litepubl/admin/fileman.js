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
count: 0,
    indialog: false,
    holder: false,
    
    init: function(options) {
      this.loaded = [],
    this.items = {};

      this.tml = litepubl.tml.fileman;
      $.replacetml(this.tml, {
        lang: lang.posteditor
      });

      options = $.extend({
        holder: '#posteditor-filelist',
//total uploaded files
        count: 0,
// current uploaded files into post
        items: false
      }, options);
      
        var self = this;
        var holder = this.holder = $(options.holder);
        holder.closest('form').submit(function() {
          $("input[name='files']", self.holder).val(self.loaded.join(','));
        });
        
      holder.on("click.toolbar", ".file-toolbar > button, .file-toolbar > a", function() {
        var button = $(this);
        var container = button.closest(".file-item");
        var idfile = container .data("idfile");
        
        if (button.hasClass("add-toolbutton")) {
          self.add(idfile);
        } else if (button.hasClass("delete-toolbutton")) {
container.remove();
          self.remove(idfile);
        } else if (button.hasClass("property-toolbutton")) {
          self.editprops(idfile, container );
        }
        
        return false;
      });
      
      holder.on("click.image", ".file-image", function() {
        self.openimage($(this));
        return false;
      });

try {
        this.init_uploader();
        if (options.items) {
this.count = options.count;
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
self.count = r.count;
            self.set_uploaded(r.files);
        } catch(e) {erralert(e);}
        },
        
        error: function(message, code) {
          $.errorbox(message);
        }
      });
    },

    set_uploaded: function(items) {
      for (var i in items) {
        var item = items[i];
        this.items[item.id] = item;
        if (!parseInt(item.parent) ) this.loaded.push(item.id);
      }
      
var owner = $("#oldfiles", this.holder);
if (this.loaded.length) {
      this.append(owner, items);
owner.removeClass("hidden");
} else {
owner.addClass("hidden");
}
    },
    
    append: function(owner, files) {
      for (var id in files) {
        if (parseInt(files[id].parent)) continue;
        owner.append(this.get_fileitem(id));
      }
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

    init_uploader: function() {
      this.uploader = new litepubl.Uploader();
      this.uploader.onupload.add($.proxy(this.uploaded, this));
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
        
        var owner = $("#newfiles", this.holder);
owner.find("#nonewfiles").hide();
owner.append(this.get_fileitem(idfile));
    } catch(e) {erralert(e);}
    },
    
    add: function(idfile) {
      if ($.inArray(idfile, this.loaded) < 0) {
        this.loaded.push(idfile);
      }
    },
    
    remove: function(idfile) {
      var i = $.inArray(idfile, this.loaded);
      if (i < 0) {
        idfile = parseInt(idfile);
        var i = $.inArray(idfile, this.loaded);
        if (i < 0) return;
      }
      
      this.loaded.splice(i, 1);
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