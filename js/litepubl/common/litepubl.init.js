/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.07
  */

(function($, ltoptions) {
  'use strict';

  var rurl = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/;
  var dom = rurl.exec(ltoptions.url);
  var href = rurl.exec(location.href.toLowerCase()) || [];
  if (dom[2] != href[2]) {
    ltoptions.url = ltoptions.url.replace(dom[2], href[2]);
    ltoptions.files = ltoptions.files.replace(dom[2], href[2]);
  }

  //without protocol for ajax calls
  ltoptions.ajaxurl = ltoptions.url.substring(ltoptions.url.indexOf(':') + 1);

  $.extend($.jsonrpcSettings, {
    url: ltoptions.ajaxurl + "/admin/jsonserver.php",
    onargs: function(args) {
      var user = litepubl.getuser();
      if (user.id) {
        var params = args.params;
        params.litepubl_user_id = user.id;
        params.litepubl_user = user.pass;
        params.litepubl_user_regservice = user.regservice;
      }
    },

    error: function(message, code) {
      $.errorbox(message);
    }
  });

}(jQuery, ltoptions));