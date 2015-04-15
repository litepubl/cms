/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
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
  
  //litepublisher namespace
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
    getjson: function(data, callback) {
      return $.ajax({
        type: "get",
        url: ltoptions.ajaxurl + "/admin/jsonserver.php",
        data: data,
        success: callback,
        dataType: "json",
        cache: ("cache" in data ? data.cache : true)
      });
    }
    
  };
  
  window.dump = function(obj) {
    alert(JSON.stringify(obj));
  };
  
  window.get_get=  function (name, url) {
    if (url) {
      var q = url.substring(url.indexOf('?') + 1);
    } else {
      var q = window.location.search.substring(1);
    }
    
    var vars = q.split('&');
    for (var i=0, l=  vars.length; i < l; i++) {
      var pair = vars[i].split('=');
      if (name == pair[0]) return decodeURIComponent(pair[1]);
    }
    return false;
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
  
    $.extend({
    
    load_script: function( url, callback ) {
      return $.ajax({
        type: 'get',
        url: url,
        data: undefined,
        success: callback,
        dataType: "script",
        cache: true
      });
    },
    
    onEscape: function (callback) {
      $(document).off('keydown.onEscape').on('keydown.onEscape',function(e){
        if (e.keyCode == 27) {
          if ($.isFunction(callback)) callback();
          e.preventDefault();
          $(document).off('keydown.onEscape');
        }
      });
    }
    
  });
  
  $.fn.replaceComment= function(html) {
    var result = html == undefined ? $(this.get(0).nodeValue) : $(html);
    $(this).before(result).remove();
    return result;
  };
  
  $.fn.findcomment = function(id) {
    return $.findcomment(this.get(0), id ? 'widgetcontent-' + id : false);
  };
  
  $.findcomment = function(node, text) {
    var result = false;
    do {
      if (result = $.nextcomment(node, text)) return result;
    } while (node = node.parentNode);
    return false;
  };
  
  $.nextcomment = function(node, text) {
    var result = false;
    do {
      if (node.nodeType  == 8) {
        if (!text || (text == node.nodeValue)) return node;
      }
      
      if (node.firstChild) {
        if (result = $.nextcomment(node.firstChild, text)) return result;
      }
    } while (node = node.nextSibling);
    
    return false;
  };
  
}(jQuery, document, window));