/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

;(function($, document){
  'use strict';

function home_resize(holder) {
if (!data || data.error) return false;

var win = $(window);
var winw = win.width();
var winh = win.height();

var data = holder.data("homeimage");
if (data.winw == winw && data.winh == winh) return;

data.winw = winw;
data.winh = winh;

var cur = winw >= data.breakpoint ? data.large : data.small;
if (cur != data.cur) {
//switch to second image if possible
if (cur.w) {
//next image loaded; success switching
data.cur = cur;
data.img.prop("src", cur.src);
} else if (!cur.src) {
//cant switch; single image
cur = data.cur;
} else {
return load_image(cur, function() {
data.winw = 0;
home_resize(self);
},

//error callback
function() {
//stay to non exists and fallback to single image
cur.src = false;
data.winw = 0;
home_resize(self);
});
}
}

var w = self.width();
var h = Math.min(winh - data.top, w, cur.h);
self.height(h);

var imgwidth = (cur.w / cur.h) * h;
data.img.width(imgwidth);
data.img.height(h);
data.img.css("left", (w - imgwidth) /2);
}

function load_image(info, callback, errorcallback) {
var image = new Image();
image.onload = function() {
this.onload = this.onerror = null;
info.w = this.width;
info.h = this.height;
callback();
};

image.onerror = function() {
this.onload = this.onerror = null;
errorcallback();
};

image.src = info.src;
}

$.fn.homeimage = function() {
if (!this.length || this.data("homeimage")) return this;

var self = this;
var data = {
cur: false,
error: false,
breakpoint: this.attr("data-smallwidth") || 768,
top: this.position().top,
winw: 0,
winh: 0,
img: false,
large: {
w: 0,
h: 0,
src: this.attr("data-image") || this.find("img").attr("src")
},

small: {
w: 0,
h: 0,
src: this.attr("data-small")
}
};

this.data("homeimage", data);

if (!data.large.src && !data.small.src) return this.hide();
var cur = $(window).width() >= data.breakpoint ? data.large : data.small;
if (!cur.src) {
cur = cur == data.small ? data.large : data.small;
}

data.cur =cur;
load_image(cur, function() {
data.img = $('<img src="' + data.cur.src + '" />').appendTo(self);
$(window).on("resize.homeimage", function() {
home_resize(self);
});

home_resize(self);
},

function() {
data.error = true;
self.hide();
alert("Error! Home image not load");
});

return this;
};

$(document).ready(function() {
$("#homeimage").homeimage();
});
  
})( jQuery, document);