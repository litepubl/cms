/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  litepubl.Uploader = Class.extend({
    progressbar: "#progressbar",
    maxsize: "100",
    types: "*.*",
    //events
    onsettings: $.noop,
    onbefore: $.noop,
    onupload: $.noop,
    oncomplete: $.noop,
    
    before: function(uploader) {
      var perm = $("#combo-idperm_upload");
      if (perm.length) uploader.addPostParam("idperm", perm.val());
      this.onbefore(uploader);
    },
    
    geturl: function() {
      var url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
      return url + '/admin/jsonserver.php?random=' + Math.random();
    },
    
    complete: function() {
      this.oncomplete(this.items);
      this.items.length = 0;
    },
    
    init: function() {
      this.items = new Array();
      var url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
      var cookie = $.cookie("litepubl_user");
      if (!cookie) cookie = $.cookie("admin");
      var self = this;
      var settings = {
        flash_url : url + "/js/swfupload/swfupload.swf",
        upload_url: url + "/admin/jsonserver.php",
        // prevent_swf_caching: false,
        post_params: {
          litepubl_user: cookie,
          litepubl_user_id: $.cookie("litepubl_user_id"),
          method: "files_upload"
        },
        
        file_size_limit : this.maxsize + " MB",
        file_types : this.types,
        file_types_description : "All Files",
        file_upload_limit : 0,
        file_queue_limit : 0,
        button_placeholder_id : "uploadbutton",
        
        /*
        custom_settings : {
          progressTarget : "fsUploadProgress",
          cancelButtonId : "btnCancel"
        },
        */
        //debug: true,
        
        file_dialog_complete_handler : function(numFilesSelected, numFilesQueued) {
        $(self.progressbar).progressbar({value: 0});
          this.setUploadURL(self.geturl());
          self.before(this);
          this.startUpload();
        },
        
        upload_start_handler : function(file) {
          return true;
        },
        
        upload_progress_handler : function(file, bytesLoaded, bytesTotal) {
          try {
            var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
            $(self.progressbar).progressbar( "value" , percent );
          } catch (ex) {
            this.debug(ex);
          }
        },
        
        upload_error_handler : function(file, errorCode, message) {
          //alert('uploadError');
          $.messagebox(lang.dialog.error, message);
        },
        
        upload_success_handler : function(file, serverData) {
          try {
            var r = $.parseJSON(serverData);
            self.items.push(r);
            self.onupload(file, r);
        } catch(e) { alert('error ' + e.message); }
        },
        
        upload_complete_handler : function(file) {
          //alert('uploadComplete' + file);
          try {
            /*  I want the next upload to continue automatically so I'll call startUpload here */
            if (this.getStats().files_queued === 0) {
              $(self.progressbar).progressbar( "destroy" );
              self.complete();
            } else {
              this.startUpload();
            }
          } catch (ex) {
            this.debug(ex);
          }
        }
        
      };
      
      // Button settings
      if (ltoptions.lang == 'en') {
        settings.button_image_url= ltoptions.files + "/js/swfupload/images/XPButtonUploadText_61x22.png";
        settings.button_width= 61;
        settings.button_height= 22;
      } else {
        settings.button_text= '<span class="upload_button">' + lang.posteditor.upload + '</span>';
        settings.button_image_url= ltoptions.files + "/js/swfupload/images/XPButtonNoText_160x22.png";
        settings.button_width =  160;
        settings.button_height= 22;
      settings.button_text_style = '.upload_button { font-family: Helvetica, Arial, sans-serif; font-size: 14pt; text-align: center; }';
        settings.button_text_top_padding= 1;
        settings.button_text_left_padding= 5;
      }
      
      try {
        this.onsettings(settings);
        this.uploader= new SWFUpload(settings);
    } catch(e) { alert('Error create swfupload ' + e.message); }
    }
    
  });
}(jQuery, litepubl, window));