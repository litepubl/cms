/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, ltoptions) {
  'use strict';
  
  var rurl = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/;
  var dom = rurl.exec(ltoptions.url);
  var href = rurl.exec(location.href.toLowerCase()) || [];
  if (dom[2] != href[2]) {
    ltoptions.url = ltoptions.url.replace(dom[2], href[2]);
    ltoptions.files = ltoptions.files.replace(dom[2], href[2]);
  }
  
  //without protocol for ajax calls
  ltoptions.ajaxurl = ltoptions.url.substring(ltoptions.url.indexOf(':') +1);

$.jsonrpcSettings.url = ltoptions.ajaxurl + "/admin/jsonserver.php";
$.jsonrpcSettings.onargs = function(args) {
      var user = litepubl.getuser();
      if (user.id) {
var params = args.params;
        params.litepubl_user_id = user.id;
        params.litepubl_user = user.pass;
        params.litepubl_user_regservice = user.regservice;
      }
};

}(jQuery, ltoptions));