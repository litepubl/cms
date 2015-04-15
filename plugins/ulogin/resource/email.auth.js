/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  "use strict";
  
  litepubl.Emailauth = Class.extend({
    callback: false,
    
    getradio: function(value) {
      return $.simpletml(litepubl.tml.radio, {
        name: 'authtype',
        value: value,
        title: lang.emailauth[value]
      });
    },
    
    open: function(callback) {
      this.callback = callback;
      var lng = lang.emailauth;
      var html = '';
      html += this.getradio('reg');
      html += this.getradio('login');
      html += this.getradio('lostpass');
      html += litepubl.tml.getedit('E-Mail', 'email-emailauth', '');
      html += litepubl.tml.getedit(lng.name, 'name-emailauth', '');
      html += litepubl.tml.getedit(lng.password, 'password-emailauth', '').replace(/text/gm, 'password');
      html += '<p id="info-status"></p>';
      
      var self = this;
      var dialog = self.dialog = $.litedialog({
        title: lng.title,
        html: html,
        width: 300,
        open: function(dialog) {
          $("input[name=authtype]", dialog).on("click", function() {
            var type = $(this).val();
            $("#info-status", dialog).text('');
            var name = $("#text-name-emailauth", dialog).parent();
            var pass = $("#password-password-emailauth", dialog).parent();
            var regbutton = $("button[data-index=0]", dialog);
            var loginbutton = $("button[data-index=1]", dialog);
            var lostpassbutton = $("button[data-index=2]", dialog);
            
            switch (type) {
              case 'reg':
              name.show();
              regbutton.show();
              pass.hide();
              loginbutton.hide();
              lostpassbutton.hide();
              break;
              
              case 'login':
              pass.show();
              loginbutton.show();
              name.hide();
              regbutton.hide();
              lostpassbutton.hide();
              break;
              
              case 'lostpass':
              name.hide();
              pass.hide();
              regbutton.hide();
              loginbutton.hide();
              lostpassbutton.show();
              break;
            }
          })
          .filter('[value=reg]').click();
          
          litepubl.stat('emailauth_open');
        },
        
        buttons: [{
          title: lng.regbutton,
          click: function() {
            var email = self.getemail();
            if (!email) return false;
            var edit = $("#text-name-emailauth", dialog);
            var name = $.trim(edit.val());
            if (name) {
              self.reg(email, name);
            } else {
              edit.focus();
            }
            
            litepubl.stat('emailauth_reg');
            return false;
          }
          
        }, {
          title: lng.loginbutton,
          click: function() {
            var email = self.getemail();
            if (!email) return false;
            var edit = $("#password-password-emailauth", dialog);
            var password = $.trim(edit.val());
            if (password) {
              self.login(email, password);
            } else {
              edit.focus();
            }
            
            litepubl.stat('emailauth_login');
            return false;
          }
          
        }, {
          title: lng.lostpassbutton,
          click: function() {
            var email = self.getemail();
            if (email) self.lostpass(email);
            
            litepubl.stat('emailauth_lostpass');
            return false;
          }
          
        }, {
          title: lang.dialog.close,
          click: $.closedialog
        }]
      });
    },
    
    getemail: function() {
      var email = $("#text-email-emailauth", this.dialog);
      var result = $.trim(email.val());
      if (result) {
      if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(result)) {
          return result;
        }
      }
      
      email.focus();
      return false;
    },
    
    disable: function(disabled) {
      $(":input", this.dialog).prop("disabled", disabled ? 'disabled="disabled"' : false);
    },
    
    success: function(r) {
      litepubl.user = r;
      set_cookie("litepubl_user_id", r.id);
      set_cookie("litepubl_user", r.pass);
      set_cookie("litepubl_regservice", 'email');
      set_cookie("litepubl_user_flag", r.adminflag);
      litepubl.ulogin.registered = true;
      litepubl.ulogin.logged = true;
      
      this.dialog = false;
      $.closedialog(this.callback);
    },
    
    login: function(email, password) {
      return this.ajax({
        method: "email_login",
      params:  {email: email, password: password},
        callback:  $.proxy(this.success, this)
      });
    },
    
    ajax: function(args) {
      this.disable(true);
      var self = this;
      args.error = function(message, code) {
        self.disable(false);
        $("#info-status", self.dialog).text(message);
      };
      
      return $.jsonrpc(args);
    },
    
    setstatus: function(status) {
      this.disable(false);
      $("input[value=login]", this.dialog).click();
      $("#password-password-emailauth", this.dialog).focus();
      $("#info-status", this.dialog).text(lang.emailauth[status]);
    },
    
    reg: function(email, name) {
      var self = this;
      return this.ajax({
        method: "email_reg",
      params:  {email: email, name: name},
        callback: function(r) {
          self.setstatus('registered');
        }
      });
    },
    
    lostpass: function(email, name) {
      var self = this;
      return this.ajax({
        method: "email_lostpass",
      params:  {email: email, name: name},
        callback:  function(r) {
          self.setstatus('restored');
        }
      });
    }
    
  });//class
  
}(jQuery, document, window));