/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  'use strict';

litepubl.classDownloadItem = Class.extend({
fileurl: "",
siteurl: "",

init: function() {
$(document).on("click.downloaditem", ".downloaditem", function() {
});

$("#change_url").click(function() {
this.dialog();
return false;
});

var url = this.getsite();
if (url) {
this.update(url);
} else {
links.click(this.clicked);
}
},

getsite: function() {
var result = get_get('site');
if (result) {
set_cookie('download_site', result);
} else {
result = get_cookie('download_site');
}

return result;
},

getitem: function(url, type) {
var args  = 'itemtype=' + type + '&url=' +encodeURIComponent(url);
var q = this.siteurl.indexOf('?')== -1  ? '?' : '&';
return this.siteurl + '/admin/service/upload/' + q + args;
},

dialog: function(callback) {
var self = this;
$.litedialog({
title: ltoptions.siteurl_dialog.title,
html: ltoptions.siteurl_dialog.html,
buttons: [
{
        title: "Ok",
        click: function() {
var url = $.trim($("input[name='text_download_site']").val());
          $.closedialog();
if (url ) {
set_cookie('download_site', url);
}
self.update(url);
if ($.isFunction(callback)) callback();
}
    },
$.get_cancel_button()
]
} );
},

clicked: function() {
var url = $(this).data("url");
var type = $(this).attr("rel");
if (!this.siteurl) {
var self = this;
this.dialog(function() {
window.location= self.getitem(url, type);
});
}

return false;
}

update: function(url) {
if ('/' == url.charAt(url.length - 1)) url = url.substring(0, url.length - 1);
if (this.siteurl ==url) return;
this.siteurl =url;
$("#text_download_site").val(url);
var link = $("#yoursite");
link.attr("href", url);
link.attr("title", url);
link.text(url);

if (url == '') {
$("a[rel='theme'], a[rel='plugin']").click(this.clicked);
} else {
$("a[rel='theme'], a[rel='plugin']").each(function() {
$(this).off("click");
var type = $(this).attr("rel");
var fileurl = $(this).data("url");
$(this).attr("href", this.getitem(fileurl, type));
});
}
}

});

$(function() {
litepubl.downloadItem = new litepubl.classDownloadItem();
});
})( jQuery );