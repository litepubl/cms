/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.04
 */

(function($, window) {
  'use strict';

  window.litepubl = {
    guid: $.now(),
    tml: {}, //namespace for templates
    adminpanel: false,
    is_adminpanel: function() {
      if (litepubl.adminpanel !== false) return litepubl.adminpanel;
      return litepubl.adminpanel = litepubl.is_admin_url(location.href);
    },

    is_admin_url: function(url) {
      url = url.toLowerCase();
      var i = url.indexOf('://');
      if (i >= 0) url = url.substring(i + 4);
      var path = url.split('/');
      if ((path.length <= 2) || (path[1] != 'admin') || (path[2] == '')) return 0;
      return /^(login|logout|password|reguser)$/.test(path[2]) ? 0 : 1;
    },

    user: 0,
    getuser: function() {
      var self = litepubl;
      if (self.user) return self.user;
      return self.user = {
        id: parseInt($.cookie('litepubl_user_id')),
        pass: $.cookie('litepubl_user'),
        regservice: $.cookie('litepubl_regservice')
      };
    },

    // current image galery
    openimage: function(image) {
      //image = {url, title, description...}
      // nothing abstract, must be assigned later
    },

    linkimage: function(link) {
      var file = link.data("file");
      var midle = link.data("midle");

      if (midle && ($(window).width() <= 768)) {
        var image = {
          url: midle.link,
          width: parseInt(midle.width),
          height: parseInt(midle.height)
        };
      } else {
        var image = {
          url: link.attr("href"),
          width: parseInt(file.width),
          height: parseInt(file.height)
        };
      }

      image.title = link.attr("title");
      image.description = $("img", link).attr("alt");

      return this.openimage(image);
    },

    //forward declaration for future plugins as yandex metrika or google analitik
    stat: function(name, param) {},

    dump: function(obj) {
      alert(JSON.stringify(obj));
    },

    showError: false,
    onerror: function(error) {
      var mesg = "error \n" + error.message;
      if ("stack" in error) {
        mesg = mesg + "\n" + error.stack;
      }

      Logger.error(mesg);

      if (this.showError) {
        alert(mesg);
      }
    }

  };

  //cookies
  window.get_cookie = function(name) {
    return $.cookie(name);
  };

  window.set_cookie = function(name, value, expires) {
    $.cookie(name, value, {
      path: '/',
      expires: expires ? expires : 3650,
      secure: "secure" in ltoptions ? ltoptions.secure : false
    });
  };

}(jQuery, window));