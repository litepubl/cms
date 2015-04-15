/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$(document).ready(function() {
      if ($("input[id^='colorbutton']").length) {
var dir = ltoptions.files + '/plugins/colorpicker/';
    $('<link rel="stylesheet" type="text/css" href="'+ dir + 'css/colorpicker.css" />').appendTo("head:first");
    $.load_script(dir + "js/colorpicker.js", function() {
$("input[id^='colorbutton']").ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$("#" + $(el).attr("rel")).val(hex);
		$(el).ColorPickerHide();
	},

	onBeforeShow: function () {
var edit = "#" + $(this).attr("rel");
$(this).ColorPickerSetColor($(edit).val());
	}
});

});
}
});