/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

(function($, window) {
  "use strict";

  litepubl.Ulogin = Class.extend({
    url: '/admin/ulogin.php?backurl=',
    autoinit: "#ulogin-autoinit",
    script: false,
//callbacks for  ulogin native events
onready: $.noop,
onopened: $.noop,
onclosed: $.noop,

    css: '',
    tml: '<div id="ulogin-dialog"><div id="ulogin-holder" data-ulogin="%%data%%"></div></div>',
    tml_admin: '<h4>%%lang.helptitle%%</h4>' +
      '<div id="ulogin-buttons" data-ulogin="%%data%%"></div>',
    tml_data: 'display=small;' +
      'fields=first_name,last_name;' +
      'optional=email,phone,nickname;' +
      'providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;' +
      'hidden=other;' +
      'redirect_uri=%%redirurl%%;' +
      '%%callback%%',

    init: function() {
      $.ready2($.proxy(this.adminbuttons, this));
    },

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

    adminbuttons: function() {
      var holder = $(this.autoinit);
      if (!holder.length) return;

      var html = $.parsetml(this.tml_admin, {
        lang: lang.authdialog,
        data: $.parsetml(this.tml_data, {
          redirurl: encodeURIComponent(ltoptions.url + this.url + encodeURIComponent(get_get('backurl'))),
          callback: ''
        })
      });

      holder.append(html);
this.initOnReady('ulogin-buttons');
    },

initOnReady: function(id) {
var self = this;
      this.ready(function() {
        uLogin.customInit(id);
$('[data-uloginbutton]', '#' + id).attr('role', 'button');

uLogin.setStateListener(id, 'ready', function(){
self.onready();
});

uLogin.setStateListener(id, 'open', function(){
self.onopened();
});

uLogin.setStateListener(id, 'close', function(){
self.onclosed();
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
      litepubl.authdialog.setstatus("info", lang.authdialog.request);
      setTimeout(function() {
        litepubl.stat('ulogin_token');
      }, 10);

      var authdialog = litepubl.authdialog;
      return $.jsonrpc({
        method: "ulogin_auth",
        params: {
          token: token
        },
        slave: authdialog.args.rpc,
        callback: $.proxy(authdialog.setuser, authdialog),
        error: function(message, code) {
          litepubl.authdialog.setstatus("error", message);
        }
      });
    }

  }); //class

}(jQuery, window));