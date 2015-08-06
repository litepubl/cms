(function ($, litepubl, window) {
  'use strict';
  
  litepubl.Headereditor = Class.extend({
file: false,
style: false,
filereader: false,
    jq: false,
    idinput: "#file-input, #dropzone",
    
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
$("#submitbutton-update").on("click.header", $.proxy(this.submit, this));
    },
    
add: function(e, file) {
this.file = file;
var css = litepubl.tml.header.replace('%%file%%', e.target.result);
alert(css);
if (this.style) this.style.remove();
this.style = $('<style type="text/css">' + css + '</style>').appendTo("head:first");
    },

submit: function() {
this.upload(this.file);

return false;
},

    setprogress: function(current, total) {
      //this.setpercent(Math.ceil((current / total) * 100));
    },

uploaded: function(resp) {
dump(resp);
},

    upload: function(file) {
      var formdata = new FormData();
      // warning: Filedata is same in flash and can not be changed
      formdata.append("image", file);
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
litepubl.headereditor = new litepubl.Headereditor();
});
  }(jQuery, litepubl, window));