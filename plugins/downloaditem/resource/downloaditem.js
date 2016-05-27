/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  'use strict';

litepubl.classDownloadItem = Class.extend({

init: function() {
$(document).on("click.downloaditem", ".downloaditem", function() {
});
},

getsite: function() {
var result = '';
if (result = get_get('site')) {
set_cookie('download_site', result);
} else {
result = get_cookie('download_site');
}

return result;
},

getitem: function(url, type) {
var args  = 'itemtype=' + type + '&url=' +encodeURIComponent(url);
var q = ltoptions.download_site.indexOf('?')== -1  ? '?' : '&';
return ltoptions.download_site + '/admin/service/upload/' + q + args;
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
if (url != '') set_cookie('download_site', url);
self.update(url);
if ($.isFunction(callback)) callback();
}
    },
{
        title: lang.dialog.cancel,
        click: function() {
          $.closedialog();
}
    }
]
} );
},

clicked: function() {
var url = $(this).data("url");
var type = $(this).attr("rel");
if (ltoptions.download_site == '') {
var self = this;
this.dialog(function() {
window.location= self.getitem(url, type);
});
}

return false;
}

update: function(url) {
if ('/' == url.charAt(url.length - 1)) url = url.substring(0, url.length - 1);
if (ltoptions.download_site ==url) return;
ltoptions.download_site =url;
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

//try {
$("#change_url").click(function() {
this.dialog();
return false;
});

if (url = this.getsite()) {
this.update(url);
} else {
ltoptions.download_site = '';
links.click(this.clicked);
}
//} catch(e) { alert('ex' + e.message); }

}

$(function() {
litepubl.downloadItem = new litepubl.classDownloadItem();
});
})( jQuery );