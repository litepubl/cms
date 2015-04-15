/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  "use strict";
  
  $(document).ready(function() {
    litepubl.ulogin = new litepubl.Ulogin();
  });
  
  litepubl.Ulogin = Class.extend({
    registered: false,
    logged: false,
    script: false,
    dialog: false,
    html: '<div><p>%%lang.subtitle%%</p>' +
    '<div id="ulogin-dialog">' +
    '<div id="ulogin-holder" data-ulogin="display=small;fields=first_name,last_name;optional=email,phone,nickname;providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;hidden=other;redirect_uri=%%redirurl%%;%%callback%%"></div></div>' +
    '<div><a href="%%url%%" id="email-login">%%lang.emaillogin%%</a></div></div>',
    
    init: function() {
      this.registered = litepubl.getuser().pass ? 1 : 0;
      if (this.registered) return;
      var self = this;
      $('a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]').click(function() {
        var url = $(this).attr("href");
        if (litepubl.is_admin_url(url)) self.open(url);
        return false;
      });
      
      $("#ulogin-comment-button").click(function() {
        self.onlogged({
          type: 'get',
          method: "comments_get_logged",
        params: {idpost: ltoptions.idpost},
          callback:  function(r) {
            $("#before-commentform").html(r);
          },
          
          error: function(message, code) {
            $.messagebox(lang.dialog.error, message);
          }
        });
        
        return false;
      });
    },
    
    open: function(args) {
      if (this.dialog) return false;
      args = $.extend({
        url: ltoptions.url + "/admin/login/?backurl=" + encodeURIComponent(location.href),
        callback: false,
        email: function() {
          window.location = args.url;
        }
      }, args);
      
      var self = this;
      self.ready(function() {
        self.dialog = true;
        var lng = lang.ulogin;
        var html = self.html.replace(/%%lang.emaillogin%%/gim, lng.emaillogin)
        .replace(/%%lang.subtitle%%/gim, lng.subtitle)
        .replace(/%%url%%/gim, args.url);
        
        if ($.isFunction(args.callback)) {
          html = html.replace(/%%callback%%/gim, "callback=ulogincallback")
          .replace(/%%redirurl%%/gim, '');
          window.ulogincallback = function(token) {
            $.closedialog();
            try {
              args.callback(token);
              litepubl.stat('ulogin_token');
          } catch(e) {erralert(e);}
          };
        } else {
          html = html.replace(/%%callback%%/gim, "")
          .replace(/%%redirurl%%/gim, encodeURIComponent(ltoptions.url + "/admin/ulogin.php?backurl=" + encodeURIComponent(args.url)));
        }
        
        $.litedialog({
          title: lng.title,
          html: html,
          width: 300,
          close: function() {
            self.dialog = false;
            litepubl.stat('ulogin_close');
          },
          
          open: function() {
            uLogin.customInit('ulogin-holder');
            
            $("#email-login").click(function() {
              $.closedialog(function() {
                if (!("emailauth" in litepubl)) litepubl.emailauth = new litepubl.Emailauth();
                litepubl.emailauth.open(args.email);
              });
              return false;
            });
            
            litepubl.stat('ulogin_open');
          },
          
          buttons: [{
            title: lang.dialog.close,
            click: $.closedialog
          }]
        });
      });
    },
    
    ready: function(callback) {
      if (this.script) return this.script.done(callback);
      return this.script = $.load_script('//ulogin.ru/js/ulogin.js', callback);
    },
    
    auth: function(token, slave, callback) {
      var self =this;
      return $.jsonrpc({
        method: "ulogin_auth",
      params:  {token: token},
        slave: slave,
        callback:  function(r) {
          litepubl.user = r;
          set_cookie("litepubl_user_id", r.id);
          set_cookie("litepubl_user", r.pass);
          set_cookie("litepubl_regservice", r.regservice);
          self.registered = true;
          self.logged = true;
          if ($.isFunction(callback)) callback();
        }
      });
    },
    
    login: function(url, slave, callback) {
      var self = this;
      self.open({
        url: url,
        callback: function(token) {
          self.auth(token, slave, callback);
        },
        
        email: callback
      });
    },
    
    logon: function(slave, callback) {
      var self = this;
      self.open({
        url: '',
        callback: function(token) {
          self.auth(token, slave, callback);
        },
        
        email: function() {
          if (slave) {
            $.jsonrpc(slave);
          } else if ($.isFunction(callback)) {
            callback();
          }
        }
      });
    },
    
    onlogged: function(slave, callback) {
      if (!this.registered) return        this.logon(slave, callback);
      
      if (this.logged) {
        if (slave) {
          $.jsonrpc(slave);
          litepubl.stat('ulogin_checklogged');
          return false;
        } else {
          if ($.isFunction(callback)) callback('logged');
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
  
}(jQuery, document, window));