/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, litepubl, window) {
  'use strict';
  litepubl.FlashUploader = Class.extend({
    owner: false,
    swf: false,
    onsettings: $.noop,
    html: '<div id="flash-uploader"><span id="uploadbutton"></span></div>',
    // no jquery selector
    idbutton: "uploadbutton",
    
    init: function(owner) {
      this.owner = owner;
      var url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
      owner.holder.append(this.html);
      var self = this;
      var settings = {
        flash_url : url + "/js/swfupload/swfupload.swf",
        upload_url: url + "/admin/jsonserver.php",
        // prevent_swf_caching: false,
        post_params: owner.postdata,
        file_size_limit : owner.maxsize + " MB",
        file_types : owner.types,
        file_types_description : "All Files",
        file_upload_limit : 0,
        file_queue_limit : 0,
        button_placeholder_id : this.idbutton,
        //debug: true,
        
        file_dialog_complete_handler : function(numFilesSelected, numFilesQueued) {
          owner.setpercent(0);
          this.setUploadURL(owner.geturl());
          owner.before(this);
          this.startUpload();
        },
        
        upload_start_handler : function(file) {
          return true;
        },
        
        upload_progress_handler : function(file, bytesLoaded, bytesTotal) {
          owner.setprogress(bytesLoaded, bytesTotal);
        },
        
        upload_error_handler : function(file, errorCode, message) {
          owner.error(message);
        },
        
        upload_success_handler : function(file, serverData) {
          try {
            owner.uploaded(serverData);
        } catch(e) { alert('error ' + e.message); }
        },
        
        upload_complete_handler : function(file) {
          //alert('uploadComplete' + file);
          try {
            /*  I want the next upload to continue automatically so I'll call startUpload here */
            if (this.getStats().files_queued === 0) {
              owner.complete();
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
        this.swf = new SWFUpload(settings);
    } catch(e) { alert('Error create swfupload ' + e.message); }
    },
    
    addparam: function(name, value) {
      this.swf.addPostParam(name, value);
    }
    
  });
}(jQuery, litepubl, window));