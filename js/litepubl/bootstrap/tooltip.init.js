/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $){
  'use strict';
  
  $.fn.settooltip = function() {
    return this.on("mouseenter.settooltip focus.settooltip",".tooltip-toggle:not(.tooltip-ready)",  function(event) {
      var self = $(this);
      self.addClass("tooltip-ready");
      if (self.data("bs.tooltip")) return;
      
      self.tooltip({
        container: 'body',
        placement: 'auto top'
      });
      
      self.trigger(event);
    });
  };
  
})( jQuery);