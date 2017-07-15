/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($, litepubl, window) {
  'use strict';

  litepubl.Homeuploader = Class.extend({
    dataname: 'image',
    filereader: false,
    jq: false,
    idinput: "#file-imgupload, #dropzone",
    status: 'init',
    helpstatus: false,

    init: function() {
      this.filereader = new window.FileReader();
      var options = {
        accept: 'image/*',
        readAsDefault: ('readAsBinaryString' in this.filereader ? 'BinaryString' : 'ArrayBuffer'),
        //readAsDefault: 'DataURL',
        on: {
          load: $.proxy(this.upload, this)
        }
      };

      $(this.idinput).fileReaderJS(options);
      $("body").fileClipboard(options);
      this.helpstatus = $("#helpstatus");
    },

    setstatus: function(name) {
      this.status = name;
      this.helpstatus.children().addClass("hide");
      this.helpstatus.find("#img-" + name).removeClass("hide");
    },

    setprogress: function(current, total) {
      if (current && total) {
        var value = Math.ceil((current / total) * 100);
      } else {
        var value = 0;
      }

      this.helpstatus.find("#img-percent").text(value + '%');
    },

    uploaded: function(resp) {
      if (resp.result == "error") {
        this.setstatus('fail');
      } else {
        this.setstatus('success');
        $("#text-image").val(resp.result.image);
        $("#text-smallimage").val(resp.result.smallimage);
      }
    },

    upload: function(e, file) {
      this.setstatus('percent');
      var formdata = new FormData();
      formdata.append(this.dataname, file);

      var self = this;
      this.jq = $.ajax({
          type: "post",
          url: location.href,
          cache: false,
          data: formdata,
          dataType: "json",
          contentType: false,
          processData: false,

          success: function(r) {
            self.uploaded(r);
          },

          xhr: function() {
            var result = $.ajaxSettings.xhr();
            if ("upload" in result) {
              result.upload.addEventListener("progress", function(event) {
                if (event.lengthComputable) {
                  self.setprogress(event.loaded, event.total);
                }
              }, false);

            }
            return result;
          }

        })
        .fail(function(jq, textStatus, errorThrown) {
          self.setstatus('fail');
          alert(jq.responseText);
        });
    }

  });

  $(function() {
    litepubl.homeuploader = new litepubl.Homeuploader();
  });
}(jQuery, litepubl, window));