/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

;(function($, document){
  'use strict';

function home_resize(holder) {
var win = $(window);
var winw = win.width();
var winh = win.height();
var data = holder.data("homeimage");
if (data && !data.error && (data.winw != winw || data.winh != winh)) {
data.winw = winw;
data.winh = winh;

var w = self.width();
var h = Math.min(winh - data.top, w);
self.height(h);

var img = data.mimg;
var imgw = (data.imgw / data.imgh) * h;
img.width(imgw);
img.height(h);
img.css("left", (w - imgw) /2);
}
}

$.fn.homeimage = function() {
if (!this.length || this.data("homeimage")) {
return this;
}

var self = this;
data = {
winw: 0,
winh: 0,
imgw: 0,
imgh: 0,
img: false,
top: this.position().top,
error: false
};

this.data("homeimage", data);

var img = new Image();
img.onload = function() {
this.onload = this.onerror = null;
data.imgw = this.width;
data.imgh = this.height;
data.img = $('<img src="' + this.src + '" />').appendTo(self);

$(window).on("resize.homeimage", function() {
home_resize(self);
});

home_resize(self);
};

img.onerror = function() {
this.onload = this.onerror = null;
data.error = true;
};

img.src = data.img.attr("src");
return this;
};

$(document).ready(function() {
$("#homeimage").homeimage();
});
  
})( jQuery, document);