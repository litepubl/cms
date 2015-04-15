/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function fileDialogComplete(numFilesSelected, numFilesQueued) {
$('#progressbar').progressbar({value: 0});
  var url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
  this.setUploadURL(url + '/admin/jsonserver.php?random=' + Math.random());
  var perm = $("#combo-idperm_upload");
  if (perm.length) this.addPostParam("idperm", perm.val());
  this.startUpload();
}

function uploadStart(file) {
  return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
  try {
    var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
    $( "#progressbar").progressbar( "value" , percent );
  } catch (ex) {
    this.debug(ex);
  }
}

function uploadError(file, errorCode, message) {
  //alert('uploadError');
  $.messagebox(lang.dialog.error, message);
}

function uploadComplete(file) {
  //alert('uploadComplete' + file);
  try {
    /*  I want the next upload to continue automatically so I'll call startUpload here */
    if (this.getStats().files_queued === 0) {
      $( "#progressbar" ).progressbar( "destroy" );
    } else {
      this.startUpload();
    }
  } catch (ex) {
    this.debug(ex);
  }
}

function createswfu (upload_success_handler) {
  var url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
  var cookie = get_cookie("litepubl_user");
  if (cookie == "") cookie = get_cookie("admin");
  var settings = {
    flash_url : url + "/js/swfupload/swfupload.swf",
    upload_url: url + "/admin/jsonserver.php",
    // prevent_swf_caching: false,
    post_params: {
      litepubl_user: cookie,
      litepubl_user_id: get_cookie("litepubl_user_id"),
      method: "files_upload"
    },
    file_size_limit : "100 MB",
    file_types : "*.*",
    file_types_description : "All Files",
    file_upload_limit : 0,
    file_queue_limit : 0,
    /*
    custom_settings : {
      progressTarget : "fsUploadProgress",
      cancelButtonId : "btnCancel"
    },
    */
    //debug: true,
    
    // Button settings
    button_image_url: ltoptions.files + "/js/swfupload/images/XPButtonUploadText_61x22.png",
    //button_text: '<span class="theFont">Hello</span>',
    button_placeholder_id : "uploadbutton",
    button_width: 61,
    button_height: 22,
    
    file_dialog_complete_handler : fileDialogComplete,
    upload_start_handler : uploadStart,
    upload_progress_handler : uploadProgress,
    upload_error_handler : uploadError,
    upload_success_handler : upload_success_handler,
    upload_complete_handler : uploadComplete
  };
  
  if (ltoptions.lang != 'en') {
    settings.button_text= '<span class="upload_button">' + lang.posteditor.upload + '</span>';
    settings.button_image_url= ltoptions.files + "/js/swfupload/images/XPButtonNoText_160x22.png";
    settings.button_width =  160;
  settings.button_text_style = '.upload_button { font-family: Helvetica, Arial, sans-serif; font-size: 14pt; text-align: center; }';
    settings.button_text_top_padding= 1;
    settings.button_text_left_padding= 5;
  }
  
  try {
    return new SWFUpload(settings);
} catch(e) { alert('Error create swfupload ' + e.message); }
}