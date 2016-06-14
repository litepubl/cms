/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 **/

(function($, document, window) {
  "use strict";

  litepubl.Emailauth = Class.extend({
    dialog: false,
    tml_edit: '<div class="input-group">' +
      '<span class="input-group-addon"><span class="fa fa-%%icon%%"></span></span>' +
      '<label class="sr-only" for="text-%%name%%" >%%title%%</label>' +
      '<input type="text" class="form-control" name="%%name%%" id="text-%%name%%" value="%%value%%" placeholder="%%title%%" />' +
      '</div>',

    css: '.modal-body .input-group-addon {' +
      'width:3em' +
      '}',

    getradio: function(value) {
      return $.simpletml(litepubl.tml.radio, {
        name: 'authtype',
        value: value,
        title: lang.authdialog[value]
      });
    },

    get_storage_email: function() {
      if ("localStorage" in window) {
        try {
          var result = window.localStorage.getItem('authdialog_email');
          return result ? result : '';
        } catch (e) {}
      }

      return '';
    },

    set_storage_email: function(email) {
      if ("localStorage" in window) {
        try {
          window.localStorage.setItem('authdialog_email', email);
        } catch (e) {}
      }
    },

    html: function() {
      var lng = lang.authdialog;
      var result =
        this.getradio('reg') +
        this.getradio('login') +
        this.getradio('lostpass') +

        $.parsetml(this.tml_edit, {
          name: 'email-emailauth',
          icon: 'envelope',
          value: this.get_storage_email(),
          title: 'E-Mail'
        }) +

        $.parsetml(this.tml_edit, {
          name: 'name-emailauth',
          icon: 'user',
          value: '',
          title: lng.name
        }) +

        $.parsetml(this.tml_edit, {
          name: 'password-emailauth',
          icon: 'lock',
          value: '',
          title: lng.password
        })
        .replace(/text/gim, 'password');

      return result;
    },

    onopen: function(dialog) {
      this.dialog = dialog;
      var checkedradio = $("#text-email-emailauth", dialog).val() ? 'login' : 'reg';
      $("input[name=authtype]", dialog).on("click.emailauth", function() {
          var type = $(this).val();

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
        .filter('[value=' + checkedradio + ']').click();

      if ("tooltip" in $.fn) {
        $("input[placeholder]", dialog)
          // add tooltip-ready class  for destroy on close dialog
          .addClass("tooltip-ready")
          .tooltip({
            container: 'body',
            placement: 'top',
            trigger: 'focus',
            title: function() {
              return $(this).attr("placeholder");
            }
          });
      }

    },

    onclose: function() {
      this.dialog = false;
    },

    buttons: function() {
      var self = this;
      var lng = lang.authdialog;

      return [{
        title: lng.regbutton,
        icon: '<span class="fa fa-user-plus"></span> ',
        click: function() {
          var email = self.getemail();
          if (!email) return false;

          var edit = $("#text-name-emailauth", self.dialog);
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
        icon: '<span class="fa fa-sign-in"></span> ',
        click: function() {
          var email = self.getemail();
          if (!email) return false;

          var edit = $("#password-password-emailauth", self.dialog);
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
        icon: '<span class="fa fa-user-secret"></span> ',
        click: function() {
          var email = self.getemail();
          if (email) self.lostpass(email);

          litepubl.stat('emailauth_lostpass');
          return false;
        }

      }, {
        title: lang.dialog.close,
        icon: '<span class="fa fa-close"></span> ',
        click: $.closedialog
      }];
    },

    getemail: function() {
      var email = $("#text-email-emailauth", this.dialog);
      var result = $.trim(email.val());
      if (result) {
        if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(result)) {
          this.set_storage_email(result);
          return result;
        }
      }

      email.focus();
      litepubl.authdialog.setstatus('error', lang.authdialog.errmail);
      litepubl.stat('emailauth_errmail');
      return false;
    },

    disable: function(disabled) {
      $(":input", this.dialog).prop("disabled", disabled);
    },

    login: function(email, password) {
      var authdialog = litepubl.authdialog;
      return this.ajax({
        method: "email_login",
        params: {
          email: email,
          password: password
        },
        slave: authdialog.args.rpc,
        callback: $.proxy(authdialog.setuser, authdialog)
      });
    },

    ajax: function(args) {
      this.disable(true);

      var self = this;
      args.error = function(message, code) {
        self.disable(false);
        litepubl.authdialog.setstatus("error", message);
      };

      litepubl.authdialog.setstatus("info", lang.authdialog.request);
      return $.jsonrpc(args);
    },

    setstatus: function(status) {
      this.disable(false);
      $("input[value=login]", this.dialog).click();
      $("#password-password-emailauth", this.dialog).focus();
      litepubl.authdialog.setstatus("success", lang.authdialog[status]);
    },

    reg: function(email, name) {
      var self = this;
      return this.ajax({
        method: "email_reg",
        params: {
          email: email,
          name: name
        },
        callback: function(r) {
          self.setstatus('registered');
        }
      });
    },

    lostpass: function(email, name) {
      var self = this;
      return this.ajax({
        method: "email_lostpass",
        params: {
          email: email,
          name: name
        },
        callback: function(r) {
          self.setstatus('restored');
        }
      });
    }

  }); //class

}(jQuery, document, window));