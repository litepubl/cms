/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';

  $.easing.easeInOutExpo= function (x, t, b, c, d) {
    if (t==0) return b;
    if (t==d) return b+c;
    if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
    return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
  };
  
  $.fn.scrollto = function(speed, callback) {
    var scrolled = false;
  $("html,body").stop().animate({scrollTop: this.offset().top},speed ? speed : 2000, "easeInOutExpo",function(){
      if (scrolled) return;
      scrolled = true;
      if (callback && $.isFunction(callback)) callback();
    });

    return this;
  };
  
})( jQuery);