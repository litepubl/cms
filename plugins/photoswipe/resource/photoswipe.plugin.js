(function( $, window, document){
  'use strict';

litepubl.photoswipePlugin = Class.extend({
links: false,
opened: false,
photoswipe: false,
holder: false,
options: {},

init: function(links) {
if (!links.length) return false;

var self = this;
this.options = {
galleryPIDs: true,
//showHideOpacity:true,
getThumbBoundsFn: function(index) {
var item = self.photoswipe.items[index];
var link = self.links.eq(item.linkindex);
var img = link.find("img");
var offset = img.offset();

return {
x: offset.left,
y: offset.top,
w: img.width()
}
}
};

this.links = links.on("click.photoswipe", function() {
self.open($(this));
return false;
});

litepubl.tml.photoswipe = $.parsetml(litepubl.tml.photoswipe, {lang: lang.photoswipe});
},

open: function(link) {
if (this.opened) return false;
this.opened = true;

var items = this.getitems(link);
this.holder = $(litepubl.tml.photoswipe).appendTo("body");
        var pswp = this.photoswipe = new PhotoSwipe( this.holder.get(0), PhotoSwipeUI_Default, items, this.options);
pswp.listen('destroy', $.proxy(this.doclose, this));
        pswp.init();
},

doclose: function() {
this.photoswipe = false;
this.holder.remove();
this.holder = false;
this.opened = false;
 },

getitems: function(link) {
var result = [];
var idpost = link.data("idpost");
var idfile = link.data("file").id;
var linkindex = 0;
var options = this.options;
options.galleryUID = parseInt(idpost);

this.links.each(function() {
linkindex++;
var $this = $(this);
if (idpost == $this.data("idpost")) {
var data = $this.data("file");
if (idfile == data.id) {
options.index = result.length;
}

result.push({
src: $this.attr("href"),
msrc: $("img", $this).attr("src"),
w: parseInt(data.width),
h: parseInt(data.height),
title: $this.attr("title"),
pid: parseInt(data.id),
linkindex: linkindex
});
}
});

return result;
}

});

$(document).ready(function() {
litepubl.photoswipe = new litepubl.photoswipePlugin($(".photo"));
});

})( jQuery, window, document );