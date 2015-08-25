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
    registered: false,
    logged: false,
    script: false,
    dialog: false,
emailauth: false,
args: false,

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
      this.registered = litepubl.getuser().pass ? 1 : 0;
      if (this.registered) return;

this.emailauth = new litepubl.Emailauth();
      var self = this;
      $(document).on("click.ulogin", 'a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]', function() {
var link = $(this);
        var url = link.attr("href");

        if (litepubl.is_admin_url(url)) {
if (link.closest("#before-commentform").length) {
self.auth_comments();
} else {
self.open(url);
}
}

        return false;
      });

$.ready2($.proxy(this.adminbuttons, this));
},

auth_comments: function() {
        this.onlogged({
          type: 'get',
          method: "comments_get_logged",
        params: {idpost: ltoptions.idpost},
          callback:  function(r) {
            $("#before-commentform").html(r);
          },
          
          error: function(message, code) {
            $.errobox(message);
          }
        });
    },

html: function() {    
var hascallback = this.args.callback && $.isFunction(this.args.callback);
if (hascallback) {
          window.ulogincallback = $.proxy(this.ontoken, this);
}

        return  $.parsetml(this.tml, {
lang:lang.ulogin,
redirurl: hascallback ? "" : encodeURIComponent(ltoptions.url + this.url + encodeURIComponent(this.args.url)),
callback : hascallback ? "callback=ulogincallback" : ""
});
},

    open: function(args) {
      if (this.dialog) return false;
        this.dialog = true;

//preload script when animating dialog
      this.ready();

      this.args = $.extend({
        url: ltoptions.url + "/admin/login/?backurl=" + encodeURIComponent(location.href),
        callback: false,
slave: false
      }, args);

        var lng = lang.ulogin;      
        $.litedialog({
          title: lng.title,
          width: 300,
          html: this.html() + this.emailauth.html(),
          buttons: this.emailauth.buttons(),

          open: function(dialog) {
self.emailauth.onopen(dialog);

      self.script.done(function() {
            uLogin.customInit('ulogin-holder');
})
.fail(function() {

});

            litepubl.stat('ulogin_open');
          },

          close: function() {
            self.dialog = false;
            self.emailauth.dialog = false;
            litepubl.stat('ulogin_close');
          }
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

setuser: function(user) {
$(document).off("click.ulogin");
          litepubl.user = user;
          set_cookie("litepubl_user_id", user.id);
          set_cookie("litepubl_user", user.pass);
          set_cookie("litepubl_regservice", user.regservice);

          this.registered = true;
          this.logged = true;

          if ($.isFunction(this.args.callback)) {
this.args.callback();
}
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
    },
    
    login: function(url, slave, callback) {
      this.open({
        url: url,
        callback: callback,
slave: slave
      });
    },
    
    logon: function(slave, callback) {
      this.open({
        url: '',
        callback: callback,
slave: slave
      });
    },
    
    onlogged: function(slave, callback) {
      if (!this.registered) {
return        this.logon(slave, callback);
}
      
      if (this.logged) {
        if (slave) {
          $.jsonrpc(slave);
          litepubl.stat('ulogin_checklogged');
          return false;
        } else {
          if ($.isFunction(callback)) {
callback('logged');
}

          return true;
        }
      }
      
      var self = this;
      $.jsonrpc({
        method: "check_logged",
      params:  {},
        slave: slave,
        callback:  function(r) {
          self.logged = true;
          if ($.isFunction(callback)) callback();
        },
        
        error: function(message, code) {
          self.logon(slave, callback);
        }
      });
      
      litepubl.stat('ulogin_checklogged');
      return false;
    }
    
  });//class
  
$(document).ready(function() {
    litepubl.ulogin = new litepubl.Ulogin();
  });

}(jQuery, document, window));