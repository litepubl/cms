/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.07
 */

(function($, litepubl, window) {
  'use strict';

  litepubl.HTMLUploader = Class.extend({
    owner: false,
    // warning: Filedata is same in flash and can not be changed
    dataname: "Filedata",
    fr: false,
    jq: false,
    queue: false,
    html: '<div id="html-uploader" class="form-group"' +
      '<label for="file-input">%%lang.upload%%</label>' +
      '<input type="file" id="file-input" name="Filedata" multiple />' +
      '<div id="dropzone" class="help-block">%%lang.dragfiles%%</div>' +
      '</div>',

    idhtml: "#file-input, #dropzone",

    init: function(owner) {
      this.owner = owner;
      this.queue = [];
      this.html = $.parsetml(this.html, {
        lang: lang.posteditor
      });

      var self = this;
      this.fr = new window.FileReader();
      var options = {
        accept: owner.mime,
        readAsDefault: ('readAsBinaryString' in this.fr ? 'BinaryString' : 'ArrayBuffer'),
        on: {
          load: function(e, file) {
            self.queue.push(file);
            if (self.queue.length == 1) {
              self.start();
            }
          },

          beforestart: function(file) {
            if (owner.maxsize && (file.size > (owner.maxsize * 1024 * 1024))) return false;
          }
        }
      };

      $(this.html).appendTo(owner.holder).find(this.idhtml).fileReaderJS(options);
      $("body").fileClipboard(options);
    },

    start: function() {
      if (this.queue.length) {
        this.uploadfile(this.queue[0]);
      }
    },

    next: function() {
      if (this.queue.length) {
        this.queue.shift();
        this.start();
        if (this.queue.length == 0) {
          this.jq = false;
          this.owner.complete();
        }
      }
    },

    add: function(file) {
      this.queue.push(file);
      this.start();
    },

    uploadfile: function(file) {
      var owner = this.owner;
      owner.before(file);

      var formdata = new FormData();
      formdata.append(this.dataname, file);

      for (var name in owner.postdata) {
        formdata.append(name, owner.postdata[name]);
      }

      owner.setprogress(0);
      var self = this;
      this.jq = $.ajax({
          type: "post",
          url: owner.geturl(),
          cache: false,
          data: formdata,
          dataType: "json",
          contentType: false,
          processData: false,

          success: function(r) {
            owner.uploaded(r);
            self.next();
          },

          xhr: function() {
            var result = $.ajaxSettings.xhr();
            if ("upload" in result) {
              result.upload.addEventListener("progress", function(event) {
                //result.upload.onprogress =function(event){
                if (event.lengthComputable) {
                  owner.setprogress(event.loaded, event.total);
                }
              }, false);

              //Download progress
              /*
              result.addEventListener("progress", function(event){
                if (event.lengthComputable) {
                  var percentComplete = event.loaded / event.total;
                }
              }, false);
              */
            }
            return result;
          }

        })
        .fail(function(jq, textStatus, errorThrown) {
          self.next();
          owner.error(jq.responseText);
        });
    }

  });
}(jQuery, litepubl, window));