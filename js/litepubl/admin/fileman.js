/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
 */

(function($, litepubl, window) {
  'use strict';

  //factory to create file manager
  litepubl.init_fileman = function(options) {
    return new litepubl.Fileman(options);
  };

  litepubl.Fileman = Class.extend({
    loaded: false, //[id, ...] current attached files to post
    items: false, // {} all files
    count: 0,
    dialog: false,
    browser: false,
    holder: false,
    newfiles: false,

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
        $("input[name='files']:first", self.holder).val(self.loaded.join(','));
      });

      this.newfiles = holder.find("#newfiles")
        .on("click.toolbar", ".file-toolbar > button, .file-toolbar > a", function() {
          var button = $(this);
          var container = button.closest(".file-item");
          var idfile = container.attr("data-idfile");

          if (button.hasClass("delete-toolbutton")) {
            container.remove();
            self.remove(idfile);
          } else if (button.hasClass("property-toolbutton")) {
            self.editprops(idfile, container);
          }

          return false;
        })
        .on("click.image", ".file-image", function() {
          self.openimage($(this).closest("[data-idfile]").attr("data-idfile"));
          return false;
        });

      holder.find("#browsefiles").on("click.browsefiles", function() {
        self.browsefiles();
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
      } catch (e) {
        erralert(e);
      }
    },

    files_getpost: function() {
      var self = this;
      $.jsonrpc({
        type: 'get',
        method: "files_getpost",
        params: {
          idpost: ltoptions.idpost
        },
        callback: function(r) {
          try {
            self.count = r.count;
            self.set_uploaded(r.files);
          } catch (e) {
            erralert(e);
          }
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
        if (!parseInt(item.parent)) this.loaded.push(item.id);
      }

      if (this.loaded.length) {
        this.append(items);
      }
    },

    append: function(files) {
      var html = "";
      for (var id in files) {
        if (!parseInt(files[id].parent)) {
          html += this.get_fileitem(id);
        }
      }

      this.newfiles.append(html);
    },

    openimage: function(id) {
      var item = this.items[id];
      var midle = parseInt(item.midle) ? this.items[item.midle] : false;
      var data = midle && ($(window).width() <= 768) ? midle : item;

      litepubl.openimage({
        url: ltoptions.files + '/files/' + data.filename,
        width: parseInt(data.width),
        height: parseInt(data.height),
        title: item.title,
        description: item.description
      });
    },

    get_fileitem: function(id) {
      var item = this.items[id];
      item.link = ltoptions.files + "/files/" + item.filename;
      var type = (item.media in this.tml) ? item.media : "file";

      if (!("previewlink" in item)) {
        item.previewlink = '';
        if (parseInt(item.preview) && (item.preview in this.items)) {
          item.previewlink = ltoptions.files + "/files/" + this.items[item.preview]["filename"];
        }
      }

      return $.parsetml(this.tml.item, {
        id: item.id,
        toolbar: this.tml.toolbar,
        content: $.parsetml(this.tml[type], item)
      });
    },

    init_uploader: function() {
      this.uploader = new litepubl.Uploader();
      this.uploader.onupload.add($.proxy(this.uploaded, this));
    },
    /*
    r = {
      id: int idfile,
      item: array fileitem,
      preview: array fileitem optimal,
      midle: array fileitem optimal
    }
    */
    uploaded: function(r) {
      try {
        var idfile = r.id;
        this.items[idfile] = r.item;
        if (parseInt(r.item.preview)) {
          this.items[r.preview.id] = r.preview;
        }

        if (parseInt(r.item.midle)) {
          this.items[r.midle.id] = r.midle;
        }

        this.add(idfile);
      } catch (e) {
        erralert(e);
      }
    },

    add: function(idfile) {
      if ($.inArray(idfile, this.loaded) < 0) {
        this.loaded.push(idfile);
        this.newfiles.append(this.get_fileitem(idfile));
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
      if (this.dialog) return false;
      var self = this;
      this.dialog = new litepubl.Filemanprops(this.items[idfile],
        function() {
          self.dialog = false;
        },

        function(r) {
          self.items[r.item.id] = r.item;
          owner.replaceWith(self.get_fileitem(idfile));
        });
    },

    browsefiles: function() {
      this.dialog = true;
      if (this.browser) {
        this.browser.open();
      } else {
        this.browser = new litepubl.Filemanbrowser(this);
      }
    }

  });

}(jQuery, litepubl, window));