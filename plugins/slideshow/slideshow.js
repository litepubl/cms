/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

  $(document).ready(function() {
function start_slideshow() {
stop_slideshow();
    $("a[rel^='prettyPhoto']").prettyPhoto({
			animation_speed: 'normal',
			autoplay_slideshow: true,
deeplinking: true,
overlay_gallery: true
})
.eq(0).click();
//} catch(e) { alert(e.message); }
return false;
}

function stop_slideshow() {
         window	.clearInterval(timer);
$("#startafter").remove();
$("#stop_slideshow").remove();
return false;
}

var seconds = 10;
var timer = window.setInterval(function() {
    $('#secondcounter').text(seconds--);
    if (seconds <= 0) start_slideshow();
}, 1000);

$("#start_slideshow, #stop_slideshow").wrapInner('<a href=""></a>');
$("#start_slideshow a").click(start_slideshow);
$("#stop_slideshow a").click(stop_slideshow);
});