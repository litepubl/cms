(function ($, litepubl, window) {
  'use strict';
  
  litepubl.Headereditor = Class.extend({
file: false,
style: false,
    jq: false,
    html: '<div id="html-uploader" class="form-group"' +
    '<label for="file-input">%%lang.upload%%</label>' +
    '<input type="file" id="file-input" name="Filedata" multiple />' +
    '<div id="dropzone" class="help-block">%%lang.dragfiles%%</div>' +
    '</div>',
    
    idhtml: "#file-input, #dropzone",
    
    init: function() {
    this.html = $.parsetml(this.html, {lang: lang.posteditor});

      var self = this;
      var fr = new window.FileReader();
      $(this.html).appendTo(owner.holder).find(this.idhtml).fileReaderJS({
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