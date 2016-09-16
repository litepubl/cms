/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($) {
  'use strict';

  var popovers = [];

  $.closepopovers = function() {
    if (!popovers.length) return;

    for (var i = popovers.length - 1; i >= 0; i--) {
      $(popovers[i]).oldpopover("hide");
    }

    popovers.length = 0;
  };

  $.fn.lazypopover = function(options) {
    if (options) this.data('lazypopover', options);
    return this.addClass('lazy-popover');
  };

  $.fn.initpopover = function(options) {
    if (this.data("bs.popover")) return this;

    var o = $.extend({
      container: 'body',
      placement: 'auto right',
      trigger: 'hover focus'
    }, options, this.data('lazypopover'));

    this.removeData('lazypopover');
    return this.popover(o)
  };

  $(function() {
    $(document)
      .on('show.bs.popover.singletip', function(event) {
        popovers.push(event.target);
      })
      .on('hide.bs.popover.singletip', function(event) {
        //remove from popovers array
        for (var i = popovers.length - 1; i >= 0; i--) {
          if (event.target === popovers[i]) {
            popovers.splice(i, 1);
            return;
          }
        }
      })

    .on('mouseenter.lazypopover focus.lazypopover click.lazypopover', '.lazy-popover', function(event) {
      var self = $(this)
        .removeClass('lazy-popover')
        .addClass('popover-toggle')
        .initpopover();

      if (event.type == 'click') {
        event.preventDefault();
        var trigger = self.data("bs.popover").options.trigger;
        if (trigger.indexOf('click') != -1) {
          self.trigger(event);
        }
      } else {
        self.trigger(event);
      }
    })

    .on("click.singlepopover", function(event) {
      if (!popovers.length) return;

      var target = event.target;
      for (var i = popovers.length - 1; i >= 0; i--) {
        if (target === popovers[i]) return;
        if ($.contains(popovers[i], target)) return;
      }

      if ($(target).closest(".popover").length) return;

      for (var i = popovers.length - 1; i >= 0; i--) {
        $(popovers[i]).popover("hide");
      }

    });
  });

})(jQuery);