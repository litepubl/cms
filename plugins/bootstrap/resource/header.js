/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.04
  */

(function($, litepubl, window) {
  'use strict';

  litepubl.Headereditor = Class.extend({
    name: 'header',
    logofile: false,
    headerfile: false,
    logo: false,
    header: false,
    filereader: false,
    jq: false,
    idinput: "#file-input, #dropzone",
    savebutton: false,
    helpstatus: false,

    init: function() {
      this.filereader = new window.FileReader();
      var options = {
        accept: 'image/*',
        //readAsDefault: ('readAsBinaryString' in this.filereader ? 'BinaryString' : 'ArrayBuffer'),
        readAsDefault: 'DataURL',
        on: {
          load: $.proxy(this.add, this),
          beforestart: $.proxy(this.checkfile, this)
        }
      };

      $(this.idinput).fileReaderJS(options);
      $("body").fileClipboard(options);

      var self = this;
      this.savebutton = $("#submitbutton-update").on("click.header", function() {
        if (self[self.name + "file"]) {
          $(this).prop("disabled", true);
          self.submit();
        }

        return false;
      });

      //anouth single radio to control image select
      $("input[name=radioplace]").on("change.place", function() {
        if ($(this).attr("value") == "header") {
          self.name = "header";
          $("#headerhelp").removeClass("hide");
          $("#logohelp").addClass("hide");
        } else {
          self.name = "logo";
          $("#headerhelp").addClass("hide");
          $("#logohelp").removeClass("hide");
        }
      });

      this.helpstatus = $("#helpstatus");
    },

    checkfile: function(file) {
      if (file.size > 30000) {
        this.setstatus('warnsize');
        return false;
      }
    },

    add: function(e, file) {
      this[this.name + "file"] = file;
      if (this[this.name]) this[this.name].remove();

      var css = litepubl.tml[this.name].replace('%%file%%', e.target.result);

      // get logo width
      if (this.name == "logo") {
        var self = this;
        var img = new Image();
        img.onload = function() {
          this.onload = null;
          css = css.replace('%%width%%', this.width);
          self.logo = $('<style type="text/css">' + css + '</style>').appendTo("head:first");
        };

        img.src = e.target.result;
      } else {
        this[this.name] = $('<style type="text/css">' + css + '</style>').appendTo("head:first");
      }
    },

    setstatus: function(name) {
      this.helpstatus.children().addClass("hide");
      this.helpstatus.find("#" + name).removeClass("hide");
    },

    submit: function() {
      this.setstatus('percent');
      this.upload(this.name, this[this.name + "file"]);
    },

    setprogress: function(current, total) {
      var value = Math.ceil((current / total) * 100);
      this.helpstatus.find("#percent").text(value + '%');
    },

    uploaded: function(resp) {
      this.savebutton.prop("disabled", false);
      if (resp.result == "ok") {
        this.setstatus('success');
      } else {
        this.setstatus('error');
      }
    },

    upload: function(name, file) {
      var formdata = new FormData();
      formdata.append(name, file);

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
          self.savebutton.prop("disabled", false);
          self.setstatus('fail');
          alert(jq.responseText);
        });
    }

  });

  $(document).ready(function() {
    litepubl.headereditor = new litepubl.Headereditor();
  });
}(jQuery, litepubl, window));