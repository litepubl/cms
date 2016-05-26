/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
$(document).ready(function() {
var links = $("a[rel='theme'], a[rel='plugin']");
if (links.length) {
// save file url's
links.each(function() {
$(this).data("url", $(this).attr("href"));
});

function get_download_site() {
var result = '';
if (result = get_get('site')) {
set_cookie('download_site', result);
} else {
result = get_cookie('download_site');
}
return result;
}

function get_download_item(url, type) {
var args  = 'itemtype=' + type + '&url=' +encodeURIComponent(url);
var q = ltoptions.download_site.indexOf('?')== -1  ? '?' : '&';
return ltoptions.download_site + '/admin/service/upload/' + q + args;
}

function siteurl_dialog(fn) {
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
update_siteurl(url);
if ($.isFunction(fn)) fn();
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
}

function download_item_clicked() {
var url = $(this).data("url");
var type = $(this).attr("rel");
if (ltoptions.download_site == '') {
siteurl_dialog(function() {
window.location= get_download_item(url, type);
});
}
return false;
}

function update_siteurl(url) {
if ('/' == url.charAt(url.length - 1)) url = url.substring(0, url.length - 1);
if (ltoptions.download_site ==url) return;
ltoptions.download_site =url;
$("#text_download_site").val(url);
var link = $("#yoursite");
link.attr("href", url);
link.attr("title", url);
link.text(url);

if (url == '') {
$("a[rel='theme'], a[rel='plugin']").click(download_item_clicked);
} else {
$("a[rel='theme'], a[rel='plugin']").each(function() {
$(this).off("click");
var type = $(this).attr("rel");
var fileurl = $(this).data("url");
$(this).attr("href", get_download_item(fileurl, type));
});
}
}

//try {
$("#change_url").click(function() {
siteurl_dialog();
return false;
});

if (url = get_download_site()) {
update_siteurl(url);
} else {
ltoptions.download_site = '';
links.click(download_item_clicked);
}
//} catch(e) { alert('ex' + e.message); }

}
});
})( jQuery );