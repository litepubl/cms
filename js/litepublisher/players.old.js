/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, document){
  $(document).ready(function() {
    $(".videofile, .audiofile").one("click.play", function() {
      var comment = widget_findcomment(this, false);
      if (comment) {
        var content = comment.nodeValue;
        $(comment).remove();
        $(this).replaceWith(content);
      }
      return false;
    });
  });
})( jQuery, document);