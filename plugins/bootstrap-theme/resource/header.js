(function ($, litepubl, window) {
  'use strict';
  
  litepubl.Headereditor = Class.extend({
file: false,
style: false,
    jq: false,
    idinput: "#file-input, #dropzone",
    
    init: function() {
      var self = this;
      var fr = new window.FileReader();
$(this.idinput).fileReaderJS({
        accept: "image/jpeg",
        readAsDefault: ('readAsBinaryString' in fr ? 'BinaryString' : 'ArrayBuffer'),
        on: {
          load: $.proxy(this.add, this)
                }
      });
    },
    
add: function(e, file) {
this.file = file;
var s = ltoptions.header_tml.replace('%%image%%', btoa(file));

if (this.style) this.style.remove();
this.style = $('<style type="text/css">' + s + '</style>').appendTo("head:first");
    },
    
    uploadfile: function(file) {
      var formdata = new FormData();
      // warning: Filedata is same in flash and can not be changed
      formdata.append("Filedata", file);
      
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
        },
        
        xhr: function() {
          var result = $.ajaxSettings.xhr();
          if ("upload" in result) {
            result.upload.addEventListener("progress", function(event){
                if (event.lengthComputable) {
                  owner.setprogress(event.loaded, event.total);
                }
              }, false);
              
            }
            return result;
          }
          
        })
        .fail( function(jq, textStatus, errorThrown) {
          self.next();
          owner.error(jq.responseText);
        });
      }
      
    });

  }(jQuery, litepubl, window));