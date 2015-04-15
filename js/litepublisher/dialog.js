/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  'use strict';
  $.messagebox = function(title, mesg, callback) {
    return $.litedialog({
      title: title,
      html: "<p>" + mesg + "</p>",
      width: 200,
      close: function() {
        if ($.isFunction(callback)) callback();
      }
    });
  };
  
  $.confirmbox= function(title, mesg, button_title1, button_title2, callback) {
    return $.litedialog({
      title: title,
      html: "<p>" + mesg + "</p>",
      width: 300,
      buttons: [
      {
        title: button_title1,
        click: function() {
          var index = $(this).data("index");
          $.closedialog();
          callback(index);
        }
      },
      
      {
        title: button_title2,
        click: function() {
          var index = $(this).data("index");
          $.closedialog();
          callback(index);
        }
      }
      ]
    });
  };
  
  $.confirmdelete= function(callback) {
    $.confirmbox(lang.dialog.confirm, lang.dialog.confirmdelete, lang.dialog.yes, lang.dialog.no, function(index) {
      if (index == 0) callback();
    });
  };
  
  $.fn.litedialog = function(buttons) {
    $.litedialog({
      title: $(this).attr("title"),
      html: $(this).html(),
      buttons: buttons
    });
    return this;
  };
  
  $.get_cancel_button = function() {
    return {
      title: lang.dialog.cancel,
      click: $.closedialog
    };
  };
  
})( jQuery );