/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.07
 */

(function($, document, window) {
  "use strict";

  litepubl.Authdialog = Class.extend({
    // cookie flag
    registered: false,
    //logged = true can be only after request to server
    logged: false,
    //current arguments to calllback
    args: false,
    //instancess
    email: false,
    ulogin: false,
    // opened flag
    dialog: false,
    statusline: false,
    tml: '<p class="help-block text-center"><a href="#" id="authdialog-help" class="dashed"><span class="fa fa-question"></span> %%lang.helptitle%%</a></p>' +
      '%%ulogin%%' +
      '%%email%%' +
      //single space for non zero height
      '<p id="authdialog-status">&nbsp;</p>',

    tml_status: '<span class="text-%%status%%">%%icon%% %%text%%</span>',

    init: function() {
      this.registered = litepubl.getuser().pass ? 1 : 0;

      this.email = new litepubl.Emailauth();
      this.ulogin = new litepubl.Ulogin();

      if (!this.registered) {
        var self = this;
        $(document).on("click.authdialog", 'a[href^="' + ltoptions.url + '/admin/"], a[href^="/admin/"]', function() {
          var link = $(this);
          var url = link.attr("href");
          if (link.closest("#before-commentform").length) {
            self.auth_comments();
          } else if (litepubl.is_admin_url(url)) {
            self.open({
              url: url
            });
          } else {
            return;
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
          params: {
            idpost: ltoptions.idpost
          },
          callback: function(r) {
            $("#before-commentform").html(r);
          },

          error: function(message, code) {
            $.errorbox(message);
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

      var lng = lang.authdialog;
      var self = this;
      $.litedialog({
        title: lng.title,
        width: 300,
        css: this.ulogin.css + this.email.css,
        html: $.parsetml(this.tml, {
          lang: lng,
          ulogin: this.ulogin.html(this.args),
          email: this.email.html()
        }),

        buttons: this.email.buttons(),
        open: function(dialog) {
          $("#authdialog-help", dialog).popover({
            container: 'body',
            delay: 120,
            html: true,
            placement: 'bottom',
            trigger: 'hover focus click',
            title: function() {
              return $(this).text();
            },

            content: function() {
              return "<ul><li>" +
                lang.authdialog.help.replace(/\n/gm, "</li><li>") +
                "</li></ul>";
            }
          });

          self.statusline = $("#authdialog-status", dialog);

          self.email.onopen(dialog);
          self.ulogin.onopen(dialog);

          litepubl.stat('authdialog_open');
        },

        close: function() {
          self.email.onclose();
          self.ulogin.onclose();

          self.statusline = false;
          self.dialog = false;
          litepubl.stat('authdialog_close');
        }
      });
    },

    setstatus: function(status, text) {
      if (status == 'error') status = 'danger';
      this.statusline.html($.parsetml(this.tml_status, {
        status: status,
        icon: $.bootstrapDialog.geticon(status),
        text: text
      }));
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
        return this.open(args);
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
        params: {},
        slave: args.rpc,
        callback: function(r) {
          self.logged = true;
          if ($.isFunction(args.callback)) {
            args.callback();
          }
        },

        error: function(message, code) {
          self.open(args);
        }
      });

      litepubl.stat('authdialog_checklogged');
      return false;
    }

  }); //class

  $(function() {
    litepubl.authdialog = new litepubl.Authdialog();
  });

}(jQuery, document, window));