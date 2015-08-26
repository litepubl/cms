/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($, document, window) {
  "use strict";

  litepubl.Ulogin = Class.extend({
url: '/admin/ulogin.php?backurl=',
autoinit: "#ulogin-autoinit",
    script: false,

    tml: '<div><p>%%lang.subtitle%%</p>' +
    '<div id="ulogin-dialog">' +
    '<div id="ulogin-holder" data-ulogin="' +
'display=small;' +
'fields=first_name,last_name;' +
'optional=email,phone,nickname;' +
'providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;' + 
'hidden=other;' +
'redirect_uri=%%redirurl%%;' +
'%%callback%%"></div></div>' +
    '<div><a href="%%url%%" id="email-login">%%lang.emaillogin%%</a></div></div>',

admintml: '<div id="ulogin-buttons" data-ulogin="' +
'display=small;fields=first_name,last_name;' +
'optional=email,phone,nickname;' +
'providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;' +
'hidden=other;' +
'redirect_uri=%%redirurl%%;"></div>',

    init: function() {
$.ready2($.proxy(this.adminbuttons, this));
},
html: function() {    
//preload script when animating dialog
      this.ready();

var hascallback = $.isFunction(this.args.callback) || (typeof this.args.slave === "object");
if (hascallback) {
          window.ulogincallback = $.proxy(this.ontoken, this);
}

        return  $.parsetml(this.tml, {
lang:lang.ulogin,
redirurl: hascallback ? "" : encodeURIComponent(ltoptions.url + this.url + encodeURIComponent(this.args.url)),
callback : hascallback ? "callback=ulogincallback" : ""
});
},
          onopen: function(dialog) {
      self.script.done(function() {
            uLogin.customInit('ulogin-holder');
})
.fail(function() {

});
},

adminbuttons: function() {
var holder = $(this.autoinit);
if (!holder.length) return;

var html = this.admintml.replace(/%%redirurl%%/gim,
 encodeURIComponent(ltoptions.url + this.url + encodeURIComponent(get_get('backurl'))));

holder.append(html);
this.ready(function() {
            uLogin.customInit('ulogin-buttons');
});
},
    
    ready: function(callback) {
      if (this.script) {
return this.script.done(callback);
}

      return this.script = $.load_script('//ulogin.ru/js/ulogin.js', callback);
    },
ontoken: function(token) {
if (this.dialog) $.closedialog();
      var result = $.jsonrpc({
        method: "ulogin_auth",
      params:  {token: token},
        slave: this.args.slave,
        callback:  $.proxy(this.setuser, this)
      });

              litepubl.stat('ulogin_token');
return result;
    }

  });//class

  });//class