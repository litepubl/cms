/**
*
 Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function set_progress(value, id) {
switch (value) {
case -1:
$(id).hide();
break;

case 0:
$(id).show();
$(id + ' .uploadedpercent').text(value + ' %');
break;

default:
$(id + ' .uploadedpercent').text(value + ' %');
}
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
set_progress(0, this.customSettings.progress);
  var url = window.location.toString();
url = url + (url.indexOf("?") == -1 ? "?" : "&");
this.setUploadURL(url + 'random=' + Math.random());
  this.startUpload();
}

function uploadStart(file) {
  return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
set_progress(Math.ceil((bytesLoaded / bytesTotal) * 100), this.customSettings.progress);
}

function uploadError(file, errorCode, message) {
set_progress(-1, this.customSettings.progress);
  alert('uploadError');
}

function uploadComplete(file) {
set_progress(-1, this.customSettings.progress);
}

//central event
function uploadSuccess(file, serverData) {
//alert(serverData);
if ("logourl" == this.customSettings.name) {
        var r = $.parseJSON(serverData);
$("#logo").css("padding-left", r.width);
$("#text-color-logowidth").val(r.width);
set_color("logourl", r.url);
} else {
set_color(this.customSettings.name, serverData);
}
}

function createswfu (type) {
  var settings = {
    flash_url : ltoptions.files + "/js/swfupload/swfupload.swf",
    upload_url: document.location,
//ltoptions.url + "/theme-generator.htm",
    // prevent_swf_caching: false,
  post_params: {"formtype": type ? 'headerurl' : 'logourl'},
    file_size_limit : type ? "4 MB" : "10 MB",
    file_types : type ? "*.jpg;*.png;*.gif" : "*.png",
    file_types_description : "Images",
    file_upload_limit : 100,
    file_queue_limit : 1,
    //debug: true,
    
    // Button settings
    button_image_url: ltoptions.files + "/js/swfupload/images/XPButtonNoText_160x22.png",
    button_text: '<span class="upload_button">' + (type ? lang.themegenerator.upload_header  : lang.themegenerator.upload_logo) + '</span>',
    button_placeholder_id : type ? "uploadbutton" : "uploadlogo",
    button_width: 160,
    button_height: 22,
      button_text_style : '.upload_button { font-family: Helvetica, Arial, sans-serif; font-size: 14pt; text-align: center; }',
    button_text_top_padding: 1,
button_text_left_padding: 5,

		custom_settings : {
name : type? "headerurl" : "logourl",
			progress: type ? "#progress_image" : "#progress_logo"
		},

    file_dialog_complete_handler : fileDialogComplete,
    upload_start_handler : uploadStart,
    upload_progress_handler : uploadProgress,
    upload_error_handler : uploadError,
    upload_success_handler : uploadSuccess,
    upload_complete_handler : uploadComplete
  };
  
   try {
    return new SWFUpload(settings);
} catch(e) { alert('Error create swfupload ' + e.message); }
}

function set_color(name, value) {
if (name == "themename") {
$("#text-themename").val(value);
return;
}

var input = 		$("#text-color-" + name);
if (input.length == 0) return;
//alert(name + '=' + value);
input.val(value);
for (var i = 0, l =ltoptions.colors.length ; i < l; i++) {
var item = ltoptions.colors[i];
if (name == item['name']) {
var propvalue = item['value'].replace('%%' + name + '%%', value);
var a = propvalue.split('%%');
if (a.length >= 2) {
var name2= a[1];
propvalue = propvalue.replace('%%' + name2 + '%%', $('#text-color-' + name2).val());
}
//alert(propvalue);
try {
var sel = item['sel'];
if (sel.indexOf(":") == -1) {
$(sel).css(item['propname'], propvalue);
} else {
$('head:first').append('<style type="text/css">' + 
sel + "{" + item['propname'] + ":" + propvalue + "}" +
'</style>');
}
} catch(e) {
alert(item['sel'] + "\n" + item['propname']  + ' = ' +propvalue);
}
/*
//alert(propvalue);
var sel = item['sel'].split(',');
for (j =0; j < sel.length; j++) {
try {
var cs = $.trim(sel[j]);
$(cs).css(item['propname'], propvalue);
} catch(e) {
alert('"' + cs + '"' + "\n" + 
item['propname']  + ' = ' +propvalue);
}
}
*/

}
}
}

function parse_ini(initext) {
var lines = initext.split("\n");
for (var i = 0, l = lines.length; i < l; i++) {
var s = $.trim(lines[i]);
if ((s == '') || (s.charAt(0) == '[')) continue;
var a = s.split('=');
if (a.length != 2) continue;

var name = $.trim(a[0]);
var value = $.trim(a[1].replace('"', "").replace('"', ""));
if ((name !== '') && (value != '')) {
set_color(name, value);
}
}
}

$(document).ready(function() {
$("#menucolors, #ini_colors").click(function() {
$("#" + $(this).attr("id") + "_toggle").slideToggle();
return false;
});

ltoptions.swfu = createswfu(true);
ltoptions.swfulogo = createswfu(false);

$("input[id^='colorbutton']").ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$(el).ColorPickerHide();
try {
set_color($(el).attr("rel"), hex);
} catch(e) { alert(e.message); }
	},

//onShow: function() {$(".colorpicker_submit").append('<a href="">submit</a>');},

	onBeforeShow: function () {
var edit = "#text-color-" + $(this).attr("rel");
$(this).ColorPickerSetColor($(edit).val());
	}
});

$("#uploadcolor").submit(function() {
parse_ini($("#inicolors").val());
$("#inicolors").val("");
return false;
});
});