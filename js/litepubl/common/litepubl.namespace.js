/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  window.litepubl = {
    guid: $.now(),
  tml: {}, //namespace for templates
    adminpanel: false,
    is_adminpanel:  function() {
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
    
    //forward declaration for future plugins as yandex metrika or google analitik
  stat: function(name, param) {},
// current image galery
openimage: function(url, title, description) {}
linkimage: function(link) {
      this.openimage(link.attr("href"), link.attr("title"), $("img", link).attr("alt"));
};
 };
  
  window.dump = function(obj) {
    alert(JSON.stringify(obj));
  };
  
  //cookies
  window.get_cookie = function(name) {
    return $.cookie(name);
  };
  
  window.set_cookie = function(name, value, expires){
    $.cookie(name, value, {
      path: '/',
      expires: expires ? expires : 3650,
      secure: "secure" in ltoptions ? ltoptions.secure : false
    });
  };
  
  window.$ready = function(fn) {
    $(document).ready(fn);
  };
  
  window.erralert = function(e) {
    alert('error ' + e.message);
  };
  
}(jQuery, document, window));