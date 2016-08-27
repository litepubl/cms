/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.04
  */

(function($) {
  'use strict';

  $.load_font = function(font_name, class_name, css_url) {
    $.load_css(css_url);
    $.wait_font(font_name, class_name);
  };

  $.load_font_part = function(font_name, class_name, part_url) {
    var css = $.get_font_face(font_name, part_url);
    $.css_loader.addtext(css);
    $.wait_font(font_name, class_name);
  };

  $.wait_font = function(font_name, class_name) {
    // sometimes font not detected without timeout
    setTimeout(function() {
      var observer = new FontFaceObserver(font_name, {
        weight: 400
      });

      observer.load(null, 5000).then(function() {
        $("body").addClass(class_name);
      });
    }, 0);
  };

  $.get_font_face = function(font_name, part_url) {
    var css = "@font-face {" +
      "font-family: '%name%';" +
      "font-style: normal;" +
      "font-weight: 400;" +
      "src: url('%url%.eot');" +
      "src: local('%name%'), local('%name%-Regular'), " +
      "url('%url%.eot?#iefix') format('embedded-opentype')," +
      "url('%url%.woff') format('woff')," +
      "url('%url%.woff2') format('woff2')," +
      "url('%url%.ttf') format('truetype')," +
      "url('%url%.svg#%name%') format('svg');" +
      "}";

    css = css.replace(/%url%/gm, part_url);
    css = css.replace(/%name%/gm, font_name);
    return css;
  };

}(jQuery));