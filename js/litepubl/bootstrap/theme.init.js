/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $, document){
  'use strict';
  
  $(document).ready(function() {
    $("textarea").filter("[name^='note'], [name='content']").editorheight();
    
    $(document).settooltip();
  });
  
  $.ready2(function() {
    $(".poppost").poppost();
    
    $(".scroll-to").on("click.scrollto", function(){
      var hash = $(this).attr("href");
      $(hash).scrollto(2000, function(){
        window.location.hash = hash;
      });
      return false;
    });
  });
  
})( jQuery, document);