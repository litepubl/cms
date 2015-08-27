(function ($, document, window) {
  "use strict";

  litepubl.Authdialog = Class.extend({
// cookie flag
    registered: false,
//logged = true can be only after request to server
    logged: false,
// flag for opened/closed popup
    dialog: false,
//current arguments to calllback
args: false,
//instancess
email: false,
ulogin: false,

    init: function() {
      this.registered = litepubl.getuser().pass ? 1 : 0;

this.email = new litepubl.Emailauth();
this.ulogin = new litepubl.Ulogin();

      if (!this.registered) {
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
}
},

auth_comments: function() {
        this.check({
rpc: {
          type: 'get',
          method: "comments_get_logged",
        params: {idpost: ltoptions.idpost},
          callback:  function(r) {
            $("#before-commentform").html(r);
          },
          
          error: function(message, code) {
            $.errobox(message);
          }
}
        });
    },

    open: function(args) {
      if (this.dialog) return false;
        this.dialog = true;
      this.args = $.extend({
url: "",
        //url: ltoptions.url + "/admin/login/?backurl=" + encodeURIComponent(location.href),
rpc: false,
 callback: false
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

var args = this.args;
if (args.url && !args.rpc && !args.callback) {
//no wait close dialog just open url
location.href = args.url;
} else if (this.dialog) {
$.closedialog(args.callback);
} else if ($.isFunction(args.callback)) {
args.callback();
}
},

/* powerfull method. If user not logged then popup dialog. return 3 results:
- true
- false
- undefined
*/

    check: function(a) {
var args = $.extend({
rpc: false,
 callback: false
}, a);

      if (!this.registered) {
return        this.open(args);
}
      
      if (this.logged) {
        if (args.rpc) {
          $.jsonrpc(args.rpc);
          litepubl.stat('authdialog_checklogged');
          return false;
}

          if ($.isFunction(args.callback)) {
args.callback('logged');
}

          return true;
      }
      
      var self = this;
      $.jsonrpc({
        method: "check_logged",
      params:  {},
        slave: args.rpc,
        callback:  function(r) {
          self.logged = true;
          if ($.isFunction(args.callback)) {
args.callback();
}
        },
        
        error: function(message, code) {
          self.open(args);
        }
      });
      
      litepubl.stat('ulogin_checklogged');
      return false;
    }

  });//class
  
$(document).ready(function() {
    litepubl.authdialog = new litepubl.Authdialog();
  });

}(jQuery, document, window));