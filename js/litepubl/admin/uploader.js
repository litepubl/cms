/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, litepubl, window) {
  'use strict';
  litepubl.Uploader = Class.extend({
    handler: false,
    postdata: false,
    url: "",
    maxsize: 100,
    mime: false, // regexp for html as 'image/*' to only accept images
    types: "*.*", // for flash uploader
    holder: false,

    init: function(options) {
      options = $.extend({
        url: (ltoptions.uploadurl == undefined ? ltoptions.url : ltoptions.uploadurl) + '/admin/jsonserver.php',
        holder: "#uploader",
        maxsize: 100,
        mime: false,
        types: "*.*"
      }, options);

      $.extend(this, options);
      this.holder = $(options.holder);

      this.onbefore = $.Callbacks();
      this.oncomplete = $.Callbacks();
      this.onupload = $.Callbacks();

      this.items = [];

      var cookie = $.cookie("litepubl_user");
      if (!cookie) {
        cookie = $.cookie("admin");
      }

      this.postdata = {
        litepubl_user: cookie,
        litepubl_user_id: $.cookie("litepubl_user_id"),
        method: "files_upload"
      };

      if ("FileReader" in window) {
        this.handler = new litepubl.HTMLUploader(this);
      } else {
        this.handler = new litepubl.FlashUploader(this);
      }
    },

    geturl: function() {
      return this.url + '?_=' + litepubl.guid++;
    },

    setpercent: function(percent) {
      litepubl.progressbar.setvalue(this.holder, percent);
    },

    setprogress: function(current, total) {
      if (current && total) {
        this.setpercent(Math.ceil((current / total) * 100));
      } else {
        this.setpercent(0);
      }
    },

    showprogress: function() {
      litepubl.progressbar.add(this.holder);
    },

    hideprogress: function() {
      litepubl.progressbar.remove(this.holder);
    },

    error: function(mesg) {
      $.messagebox(lang.dialog.error, mesg);
    },

    uploaded: function(resp) {
      try {
        if (typeof resp == "string") {
          resp = $.parseJSON(resp);
        }

        if ("result" in resp) {
          this.items.push(resp.result);
          this.onupload.fire(resp.result);
        } else if ("error" in resp) {
          this.error(resp.error.message);
        }
      } catch (e) {
        erralert(e);
      }
    },

    addparam: function(name, value) {
      if ("addparam" in this.handler) {
        this.handler.addparam(name, value);
      } else {
        this.postdata[name] = value;
      }
    },

    addparams: function() {
      var perm = $("#combo-idperm_upload", this.holder.parent());
      if (perm.length) {
        this.addparam("idperm", perm.val());
      }
    },

    before: function() {
      this.addparams();
      this.onbefore.fire(this);
      this.showprogress();
    },

    complete: function() {
      this.hideprogress();
      this.oncomplete.fire(this, this.items);
      this.items.length = 0;
    }

  });
}(jQuery, litepubl, window));