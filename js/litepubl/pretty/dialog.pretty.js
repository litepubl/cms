/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($) {
  'use strict';

  $.closedialog = function(callback) {
    $.prettyPhoto.close();

    if ($.isFunction(callback)) {
      setTimeout(function() {
        callback();
      }, 220);
    }
  };

  $.litedialog = $.prettyPhotoDialog = function(o) {
    var options = $.extend({
      title: "",
      html: "",
      width: 300,
      css: false,
      open: $.noop,
      close: $.noop,
      buttons: [{
        title: "Ok",
        click: function() {
          $.prettyPhoto.close();
        }
      }]
    }, o);

    var button = '<button type="button" class="button pp_dialog_btn_%%index%%" data-index="%%index%%"><span>%%title%%</span></button>';
    var buttons = '';
    for (var i = 0, l = options.buttons.length; i < l; i++) {
      buttons += $.simpletml(button, {
        index: i,
        title: options.buttons[i].title
      });
    }

    var style = options.css ? $('<style type="text/css">' + options.css + '</style>').appendTo('head:first') : false;
    var id = "pp_dialog_id_" + litepubl.guid++;
    var div = $('<div CLASS="HIDDEN" id="' + id + '"></div>').appendTo("body");
    div.html('<div class="pp_dialog_title">' +
      '<h3>' + options.title + '</h3></div>' +
      options.html +
      '<div class="pp_dialog_buttons">' + buttons + '</div>')

    var tmp = $('<div></div>').appendTo('body').hide();
    var a = $("<a title=''></a>").appendTo(tmp);
    a.attr("href", "#" + id);

    $(document).off('keydown.prettyphoto');
    a.prettyPhoto({
      default_width: options.width,
      opacity: 0.60,
      /* Value between 0 and 1 */
      modal: true,
      /* If set to true, only the close button will close the window */
      deeplinking: false,
      /* Allow prettyPhoto to update the url to enable deeplinking. */
      keyboard_shortcuts: false,
      /* Set to false if you open forms inside prettyPhoto */
      show_title: false,
      /* true/false */
      social_tools: false,
      //hideflash: true,

      changepicturecallback: function() {
        div.remove();
        $(".pp_close").remove();
        for (var i = 0, l = options.buttons.length; i < l; i++) {
          $(".pp_dialog_btn_" + i).data("index", i).click(options.buttons[i].click);
        }
        $(".pp_dialog_btn_0").focus();
        if ($.isFunction(options.open)) options.open($(".pp_inline"));
      },
      /* Called when prettyPhoto is closed */
      callback: function() {
        $(document).off('keydown.onEscape');
        if (style) style.remove();
        if ($.isFunction(options.close)) options.close();
      }
    });

    a.click();
    tmp.remove();

    $.onEscape($.proxy($.prettyPhoto.close, $.prettyPhoto));
    return options;
  };

})(jQuery);