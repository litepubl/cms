/**
 * Lite Publisher
 * Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt)
 * and GPL (gpl.txt) licenses.
 **/

(function($, document) {
  'use strict';

  if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    $(document).ready(function() {
      $('<style type="text/css">@-ms-viewport{width:auto!important}</style>').appendTo("head:first");
    });
  }
}(jQuery, document));