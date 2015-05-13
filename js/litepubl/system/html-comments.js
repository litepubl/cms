/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($) {
  'use strict';
  
  $.fn.replaceComment= function(html) {
    var result = html == undefined ? $(this.get(0).nodeValue) : $(html);
    $(this).before(result).remove();
    return result;
  };
  
  $.fn.findcomment = function(id) {
    return $.findcomment(this.get(0), id ? 'widgetcontent-' + id : false);
  };
  
  $.findcomment = function(node, text) {
    var result = false;
    do {
      if (result = $.nextcomment(node, text)) return result;
    } while (node = node.parentNode);
    return false;
  };
  
  $.nextcomment = function(node, text) {
    var result = false;
    do {
      if (node.nodeType  == 8) {
        if (!text || (text == node.nodeValue)) return node;
      }
      
      if (node.firstChild) {
        if (result = $.nextcomment(node.firstChild, text)) return result;
      }
    } while (node = node.nextSibling);
    
    return false;
  };
  
}(jQuery));