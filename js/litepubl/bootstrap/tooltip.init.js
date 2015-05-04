/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
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
  
  $.fn.removetooltip = function() {
    this.find("tooltip-toggle").each(function() {
      var data = $(this).data("bs.tooltip");
      if (data) {
        clearTimeout(data.timeout);
        if (("$tip" in data) && data.$tip) data.$tip.remove();
      }
    });
    
    return this;
  };
  
})( jQuery);