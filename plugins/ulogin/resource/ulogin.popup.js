/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($, litepubl, window) {
  "use strict";

  litepubl.Ulogin = Class.extend({
    url: '/admin/ulogin.php?backurl=',
    script: false,
    //status values: wait, script, ready, open, close, receive, token
    status: 'wait',

    css: '',
    tml: '<div id="ulogin-dialog"><div id="ulogin-holder" data-ulogin="%%data%%"></div></div>',
    tml_data: 'display=small;' +
      'fields=first_name,last_name;' +
      'optional=email,phone,nickname;' +
      'providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;' +
      'hidden=other;' +
      'redirect_uri=%%redirurl%%;' +
      '%%callback%%',

    init: function() {},

    html: function(args) {
      //preload script when animating dialog
      this.ready();

      var hascallback = $.isFunction(args.callback) || (typeof args.rpc === "object");
      if (hascallback) {
        window.ulogincallback = $.proxy(this.ontoken, this);
      }

      return this.tml.replace(/%%data%%/gim, $.parsetml(this.tml_data, {
        redirurl: hascallback ? "" : encodeURIComponent(ltoptions.url + this.url + encodeURIComponent(args.url)),
        callback: hascallback ? "callback=ulogincallback" : ""
      }));
    },

    onopen: function(dialog) {
      this.initOnReady('ulogin-holder');
      this.script.fail(function() {
        $("#ulogin-dialog").remove();
      });
    },

    onclose: function() {
      //noop
    },

    autoinit: function(holder, buttons) {
      holder = $(holder);
      if (!holder.length) return;
      buttons = $(buttons, holder);
      $('.header-help', holder).text(lang.authdialog.helptitle);

      var backurl = get_get('backurl');
      var data = $.parsetml(this.tml_data, {
        redirurl: encodeURIComponent(ltoptions.url + this.url + (backurl ? encodeURIComponent(backurl) : '')),
        callback: ''
      });

      buttons.attr('data-ulogin', data);
      this.initOnReady(buttons.attr('id'));
    },

    initOnReady: function(id) {
      var self = this;
      this.ready(function() {
        self.status = 'script';
        uLogin.setStateListener(id, 'ready', function() {
          self.status = 'ready';
        });

        uLogin.customInit(id);
        $('[data-uloginbutton]', '#' + id).attr('role', 'button');

        uLogin.setStateListener(id, 'open', function() {
          self.status = 'open';
        });

        uLogin.setStateListener(id, 'close', function() {
          self.status = 'close';
        });

        uLogin.setStateListener(id, 'receive', function() {
          self.status = 'receive';
        });
      });
    },

    ready: function(callback) {
      if (this.script) {
        return this.script.done(callback);
      }

      return this.script = $.load_script('//ulogin.ru/js/ulogin.js', callback);
    },

    ontoken: function(token) {
      this.status = 'token';
      var authdialog = litepubl.authdialog;
      authdialog.setstatus("info", lang.authdialog.request);
      setTimeout(function() {
        litepubl.stat('ulogin_token');
      }, 10);

      return $.jsonrpc({
        method: "ulogin_auth",
        params: {
          token: token
        },
        slave: authdialog.args.rpc,
        callback: $.proxy(authdialog.setuser, authdialog),
        error: function(message, code) {
          authdialog.setstatus("error", message);
        }
      });
    }

  }); //class

}(jQuery, litepubl, window));