/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';

  $(document).ready(function() {
var tipoptions = {
selector: "button",
title: function() {
return $(this).attr("title");
}
};

var comtheme = ltoptions.theme.comments;
comtheme.comments.tooltip(tipoptions);
comtheme.holdcomments.tooltip(tipoptions);
  });
}(jQuery, document, window));