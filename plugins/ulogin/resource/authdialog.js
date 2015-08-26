(function ($, document, window) {
  "use strict";

  litepubl.Authdialog = Class.extend({
    registered: false,
    logged: false,
    dialog: false,
args: false,
email: false,
ulogin: false,

    init: function() {
      this.registered = litepubl.getuser().pass ? 1 : 0;
      if (this.registered) return;

this.email = new litepubl.Emailauth();
this.ulogin = new litepubl.Ulogin();

      var self = this;
      $(document).on("click.authdialog", 'a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]', function() {
var link = $(this);
        var url = link.attr("href");

        if (litepubl.is_admin_url(url)) {
if (link.closest("#before-commentform").length) {
self.auth_comments();
} else {
self.open({url: url});
}
}

        return false;
      });
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
    open: function(args) {
      if (this.dialog) return false;
        this.dialog = true;

      this.args = $.extend({
        url: ltoptions.url + "/admin/login/?backurl=" + encodeURIComponent(location.href),
        callback: false,
rpc: false
      }, args);

        $.litedialog({
          title: lang.ulogin.title,
          width: 300,
          html: this.ulogin.html(this.args) + this.email.html(),
          buttons: this.email.buttons(),

          open: function(dialog) {
self.email.onopen(dialog);
self.ulogin.onopen(dialog);
            litepubl.stat('authdialog_open');
          },

          close: function() {
            self.dialog = false;
            self.email.dialog = false;
            litepubl.stat('authdialog_close');
          }
      });
    },

setuser: function(user) {
$(document).off("click.authdialog");
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
    
    check: function(a) {
var args = this.extargs(a);

      if (!this.registered) {
return        this.logon(slave, callback);
}
      
      if (this.logged) {
        if (slave) {
          $.jsonrpc(slave);
          litepubl.stat('authdialog_checklogged');
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
    },

extargs: function(args) {
return $.extend({
url: "",
rpc: false,
 callback: false
}, a);
}
    
  });//class
  
$(document).ready(function() {
    litepubl.authdialog = new litepubl.Authdialog();
  });

}(jQuery, document, window));