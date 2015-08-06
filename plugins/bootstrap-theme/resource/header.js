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
        accept: 'image/*',
        readAsDefault: ('readAsBinaryString' in fr ? 'BinaryString' : 'ArrayBuffer'),
        on: {
          load: $.proxy(this.add, this)
                }
      });
    },
    
add: function(e, file) {
this.file = file;
var css = ltoptions.header_tml.replace('%%image%%', btoa(file));

if (this.style) this.style.remove();
this.style = $('<style type="text/css">' + css + '</style>').appendTo("head:first");
    },

    setprogress: function(current, total) {
      this.setpercent(Math.ceil((current / total) * 100));
    },

uploaded: function(resp) {

},

    upload: function(file) {
      var formdata = new FormData();
      // warning: Filedata is same in flash and can not be changed
      formdata.append("Filedata", file);
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

  }(jQuery, litepubl, window));