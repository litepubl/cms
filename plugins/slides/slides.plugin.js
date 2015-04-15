/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

  $(document).ready(function() {
    var images = $("a[rel^='prettyPhoto']");
if (images.length  == 0 ) return;
var dir = ltoptions.files + "/plugins/slides/";
$.load_css(dir + "slides.css");
/*
$.load_script(dir + "jquery.easing.1.3.js");
$.load_script(dir + "slides.min.jquery.js");
$.load_script(dir + "jquery.myimgscale-0.2.min.js", function() {
*/
$.load_script(dir + "jquery-plugins.js", function() {
var holder = $("#slides-holder");
var slides = $('<div class="slides_container"></div>').appendTo(holder);
var navi = $('<div class="slides_navi"></div>').appendTo(holder);
$('<a href="#" class="prev_slide"><img src="' + dir + 'img/arrow-prev.png" width="24" height="43" alt="Arrow Prev"></a>').appendTo(navi);
$('<a href="#" class="next_slide"><img src="' + dir + 'img/arrow-next.png" width="24" height="43" alt="Arrow Next"></a>').appendTo(navi);
var a = new Array();
images.each(function(index) {
var d = $.Deferred();
a.push(d);
var img =new Image();
img.onload = function() {
$(this).appendTo(slides);
$(this).scaleImage();
//alert($(this).width () + ':' + $(this).height());
d.resolve();
};

img.onerror = function() {
d.resolve();
};

img.src = $(this).attr("href");
});

      var w = $.when.apply($, a);
      w.done(function() {
			$(holder).slides({
				preload: false,
				//preloadImage: 'img/loading.gif',
	generateNextPrev: false,
next : 'next_slide',
prev : 'prev_slide',
pagination : false,
generatePagination : false,
				play: 5000,
				pause: 2500,
				hoverPause: true
			});
//alert('slides ' + slides.width() + ':' + slides.height());
//alert(holder.width() + ':' + holder.height());
});
});
});