/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
  */

(function($) {
  'use strict';

  $(document).ready(function() {

    function set_lowvision(on) {
      var method = on ? "addClass" : "removeClass";
      $("body")[method]("lowvision");
      $(".btn")[method]("btn-lg");
      $(".form-control")[method]("input-lg");
      $(".form-group")[method]("form-group-lg");
      $(".btn-group")[method]("btn-group-lg");
      $(".pagination")[method]("pagination-lg");
    }

    if ($.cookie("lowvision") == "on") {
      set_lowvision(true);
    }

    $(".switch-lowvision").click(function() {
      if ($.cookie("lowvision") == "on") {
        $.cookie("lowvision", false);
        set_lowvision(false);
      } else {
        set_cookie("lowvision", "on");
        set_lowvision(true);
      }

      return false;
    });
  });
}(jQuery));