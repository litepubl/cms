/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $, document){
  'use strict';
  
  $(document).ready(function() {
    $(document).on("click.anpost", ".anpost-title", function(event) {
      if (event.target.nodeName.toLowerCase() != "a") {
        location.href = $(event.target).find("a").attr("href");
      }
    });
  });
})( jQuery, document);