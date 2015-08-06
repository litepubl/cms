/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($, litepubl, window) {
  'use strict';
  
  litepubl.Headereditor = Class.extend({
    file: false,
    style: false,
    filereader: false,
    jq: false,
    idinput: "#file-input, #dropzone",
    savebutton: false,
    helpstatus:false,
    
    init: function() {
      this.filereader = new window.FileReader();
      var options = {
        accept: 'image/*',
        //readAsDefault: ('readAsBinaryString' in this.filereader ? 'BinaryString' : 'ArrayBuffer'),
        readAsDefault: 'DataURL',
        on: {
          load: $.proxy(this.add, this)
        }
      };
      
      $(this.idinput).fileReaderJS(options);
      $("body").fileClipboard(options);
      
      var self = this;
      this.savebutton = $("#submitbutton-update").on("click.header", function() {
        if (self.file) {
          $(this).prop("disabled", true);
          self.submit();
        }
        
        return false;
      });
      
      this.helpstatus= $("#helpstatus");
    },
    
    add: function(e, file) {
      this.file = file;
      var css = litepubl.tml.header.replace('%%file%%', e.target.result);
      if (this.style) this.style.remove();
      this.style = $('<style type="text/css">' + css + '</style>').appendTo("head:first");
      
      this.setstatus('warnsize', file.size > 30000);
    },
    
    setstatus: function(name, show) {
      this.helpstatus.children().addClass("hide");
      if (show) this.helpstatus.find("#" + name).removeClass("hide");
    },
    
    submit: function() {
      this.setstatus('percent', true);
      this.upload("image", this.file);
    },
    
    setprogress: function(current, total) {
      var value = Math.ceil((current / total) * 100);
      this.helpstatus.find("#percent").text(value + '%');
    },
    
    uploaded: function(resp) {
      this.savebutton.prop("disabled", false);
      this.setstatus('success', true);
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
            result.upload.addEventListener("progress", function(event){
              if (event.lengthComputable) {
                self.setprogress(event.loaded, event.total);
              }
            }, false);
            
          }
          return result;
        }
        
      })
      .fail( function(jq, textStatus, errorThrown) {
        self.savebutton.prop("disabled", false);
        self.setstatus('fail', true);
        alert(jq.responseText);
      });
    }
    
  });
  
  $(document).ready(function() {
    litepubl.headereditor = new litepubl.Headereditor();
  });
}(jQuery, litepubl, window));