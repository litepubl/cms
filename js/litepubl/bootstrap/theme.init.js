/**
* Litepublisher shop script
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Comercial license. IMPORTANT: THE SOFTWARE IS LICENSED, NOT SOLD. Please read the following License Agreement (plugins/shop/license.txt)
* You can use one license on one website
**/

(function( $, document){
  'use strict';

  $(document).ready(function() {
    $("textarea").filter("[name^='note'], [name='content']").editorheight();
      });

$.ready2(function() {
    $(document).settooltip();
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