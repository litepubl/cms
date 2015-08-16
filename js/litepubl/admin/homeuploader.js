(function ($, litepubl, window) {
  'use strict';
  
  litepubl.Homeuploader = Class.extend({
    dataname: 'image',
    filereader: false,
    jq: false,
    idinput: "#file-input, #dropzone",
    helpstatus:false,
    
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
      
    },

    setprogress: function(current, total) {
      var value = Math.ceil((current / total) * 100);
      this.helpstatus.find("#percent").text(value + '%');
    },
    
    uploaded: function(resp) {
      if (resp.result == "ok") {
        this.setstatus('success', true);
      } else {
        this.setstatus('error', true);
      }
    },
    
    upload: function(e, file) {
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
        alert(jq.responseText);
      });
    }
    
  });
  
  $(document).ready(function() {
    litepubl.homeuploader  = new litepubl.Homeuploader();
  });
}(jQuery, litepubl, window));