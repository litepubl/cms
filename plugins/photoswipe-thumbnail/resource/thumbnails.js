/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $, litepubl, document){
  'use strict';
  
  $(document).ready(function() {
    //delete options if already created
    litepubl.photoswipe.options = false;
    litepubl.photoswipe.animatethumbs = true;
  });
  
})( jQuery, litepubl, document );