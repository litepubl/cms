/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  $.fn.litetabs = function(params) {
    if (params == "select") return setselected(arguments[1]);
    if (params == "setindex") return setindex(this, arguments[1]);
    
    var taboptions = $.extend({
      select: $.noop,
      show: $.noop
    }, params);
    
    function setselected(a) {
      var link = $(a);
      var id = link.attr("href");
      id = id.substring(id.indexOf("#"));
      var content = $(id);
      var owner = link.closest("ul");
      var taboptions = owner.data("taboptions");
      if (taboptions.select(content) === false) return false;
      
      owner.find("a.active").each(function() {
        var self = $(this);
        self.removeClass("active");
        id = self.attr("href");
        id = id.substring(id.indexOf("#"));
        $(id).hide();
      });
      
      link.addClass("active");
      content.show();
      taboptions.show(content);
      return true;
    }
    
    function setindex(list, index) {
      return setselected($("a", list).get(index));
    }
    
    $(this).each(function(){
      $(this).data("taboptions", taboptions);
      setindex(this, 0);
    });
    
    $(this).off("click.litetabs").on("click.litetabs", "a", function(e){
      try {
        if (!$(this).hasClass("active")) setselected(this);
        e.preventDefault();
    } catch(e) { alert('error ' + e.message); }
    });
    return this;
  };
}(jQuery, document, window));