/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.03
  */

(function($) {
  'use strict';

  $.fn.flytext = function(options) {
    options = $.extend({
      text: "",
      target: false,
      callback: false,
      speed: 1400
    }, options);

    var div = $('<div>' + options.text + '</div>').appendTo('body')
      .css({
        display: "block",
        overflow: "hidden",
        position: "absolute",
        opacity: 0.5,
        zIndex: 2050,
        //border: 1px solid transparent;
        borderWidth: "1px",
        borderStyle: "solid",
        borderColor: "black",
        borderRadius: "4px"
      })
      .data("callback", options.callback);

    div.offset(this.offset());
    div.animate($(options.target).offset(), options.speed, function() {
      var self = $(this);
      var callback = self.data("callback");
      self.remove();
      if (callback && $.isFunction(callback)) callback();
    });

    return this;
  };

})(jQuery);