/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function( $, litepubl){
  'use strict';

litepubl.classDownloadItem = Class.extend({
siteurl: "",

init: function() {
var self = this;
$(document).on("click.downloaditem", ".downloaditem", function() {
self.uploadFile($(this));
return false;
});

this.siteurl = get_get('site');
if (this.siteurl) {
set_cookie('download_site', this.siteurl);
} else {
this.siteurl = get_cookie('download_site');
}
},

uploadFile: function(link) {
var fileurl = link.attr("href");
var type = link.attr("data-type");

if (this.siteurl) {
window.location= this.geturl(this.siteurl, fileurl, type);
} else {
this.dialog(fileurl, type);
}
},

geturl: function(siteurl, fileurl, type) {
var q = siteurl.indexOf('?')== -1  ? '?' : '&';
return siteurl + '/admin/service/upload/' + q + 
'itemtype=' + type + '&url=' +encodeURIComponent(fileurl);
},

dialog: function(fileurl, type) {
var self = this;
$.litedialog({
title: lang.downloaditem.title,
html: litepubl.tml.getedit(lang.downloaditem.editsite, 'editsite', ''),
buttons: [
{
        title: "Ok",
        click: function() {
var url = $.trim($("#text-siteurl").val());
if (url ) {
set_cookie('download_site', url);
window.location= self.geturl(url, fileurl, type);
}
}
    },
$.get_cancel_button()
]
} );
}

});

$(function() {
litepubl.downloadItem = new litepubl.classDownloadItem();
});
})( jQuery, litepubl);