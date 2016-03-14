/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($) {
  'use strict';

  var tooltips = [];

  $.closetooltips = function() {
    if (!tooltips.length) return;

    for (var i = tooltips.length - 1; i >= 0; i--) {
      $(tooltips[i]).tooltip("hide");
    }

    tooltips.length = 0;
  };

  $.fn.settooltip = function(selector, options) {
    selector = selector || ".tooltip-toggle";
    return this.on("mouseenter.settooltip focus.settooltip", selector + ":not(.tooltip-ready)", function(event) {
      var self = $(this);
      self.addClass("tooltip-ready");
      if (self.data("bs.tooltip")) return;

      self.tooltip({
          container: 'body',
          placement: 'auto top'
        }, options)
        .on('show.bs.tooltip.singletip', function() {
          tooltips.push(this);
        })
        .on("hide.bs.tooltip.singletip", function() {
          //remove from tooltips array
          for (var i = tooltips.length - 1; i >= 0; i--) {
            if (this === tooltips[i]) {
              tooltips.splice(i, 1);
              return;
            }
          }
        });

      self.trigger(event);
    });
  };

})(jQuery);