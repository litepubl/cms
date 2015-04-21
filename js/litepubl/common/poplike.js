(function( $, window){
  'use strict';
  
  //namespace
  var poplike = window.poplike = {
    man: false,
    getman: function() {
      if (!this.man) {
        this.man = new this.Manager();
      }
      
      return this.man;
    }
  };
  
  poplike.Manager = Class.extend({
    guid: 0,
    cache: false,
    domcache: false,
    tml_button: '<div class="poplike-button" id="poplike-button-%%id%%" data-net="%%netname%%" data-url="%%url%%" data-title="%%title%%" data-status="new"></div>',
    tml_share: '<a target="_blank" class="btn btn-default" role="button" href="%%url%%"><span class="fa fa-%%name%%"></span><span class="sr-only">%%name%%</span> %%title%%</a>',
    
    init: function() {
      this.guid = $.now();
      this.domcache = $('<div class="hide hidden"></div>').appendTo('body');
      this.cache = [];
    },
    
    load: function(net, callback) {
      if (net.ready) {
        if (callback) net.ready.done(callback);
        return net.ready;
      }
      
      net.ready = $.Deferred();
      if (callback) net.ready.done(callback);
      var url = net.api;
      if ("lang" in net) url = url.replace('%%lang%%', net.lang);
      
      $.ajax({
        type: "get",
        url: url,
        data: undefined,
        dataType: "script",
        cache: true,
        success: function() {
          window.setTimeout(function() {
            //set global variable from local variable after load button api
            //eval is single way to create global variable from local area
            if (net.varname && !(net.varname in window)) window[net.varname] = eval(net.varname);
            
            try {
              //if we have loaded method then call it. Exit if loaded returns false
              if (("afterload" in net) && $.isFunction(net.afterload)) {
                if (net.afterload() === false) return;
              }
              
              net.ready.resolve();
          } catch(e) { alert('error ' + e.message); }
          }, 0);
        }
      })
      .fail( function(jq, textStatus, errorThrown) {
        net.ready.reject();
      });
    },
    
    newbutton: function(netname, url, title) {
      return $.simpletml(this.tml_button, {
        id: this.guid++,
        netname: netname,
        url: url,
        title: title
      });
    },
    
    getshare: function(net, url, title) {
      return $.simpletml(this.tml_share, {
        name: net.name,
        title: lang.poplike.like,
        url: $.simpletml(net.share, {
          url: encodeURIComponent(url),
          title: encodeURIComponent(title),
          appid: "appid" in net ? net.appid : "",
          lang: "lang" in net ? net.lang : 'en'
        })
      });
    },
    
    getvalue: function(obj, value) {
      if (typeof value === "string") return falue;
      if (typeof value === "function") return value.call(obj);
      if (typeof value === "object") return value.toString();
      return false;
    },
    
    addcache: function(url, netname, button) {
      this.domcache.append(button);
      
      if (!this.getcache(url, netname)) {
        this.cache.push({
          url: url,
          net: netname,
          button: button
        });
      }
    },
    
    getcache: function(url, netname) {
      for (var i = this.cache.length - 1; i>= 0; i--) {
        var item = this.cache[i];
        if ((item.url == url) && (item.net == netname)) return item.button;
      }
      
      return false;
    }
    
  });
  
  poplike.facebook = {
    ready: false,
    name: "facebook",
    varname: 'FB',
    lang: 'en_US', //ru_RU
    api: '//connect.facebook.net/%%langcode%%/sdk.js',
    //share: 'http://www.facebook.com/sharer.php?u=%%url%%&amp;t=%%title%%',
    share: 'http://www.facebook.com/plugins/share_button.php?href=%%url%%&layout=button_count',
    share: 'https://www.facebook.com/dialog/feed?app_id=%%appid%%&link=%%url%%&name=%%title%%&caption=%%title%%&display=popup',
    /*
    &redirect_uri=
    &picture=[LINK_TO_IMAGE]' +
    */
    
    appid: false,
    width: 90,
    height: 65,
    tml: '<div class="fb-like" data-action="like" data-colorscheme="light" data-font="segoe ui" data-href="%%url%%" data-layout="box_count" data-width="90" data-height="65" data-show-faces="false" data-share="false"></div>',
    
    afterload: function() {
      FB.init({
        appId      : this.appid,
        xfbml      : false,
        version    : 'v2.1'
      });
    },
    
    onlike: function(like, unlike) {
      FB.Event.subscribe('edge.create', like);
      FB.Event.subscribe('edge.remove', unlike);
    },
    
    button: function(container, url, title, callback) {
      var html = $.simpletml(this.tml, {
        url: url,
        title: title
      });
      
      FB.XFBML.parse($(html).appendTo(container).get(0), function() {
        if ($.isFunction(callback)) callback();
      });
    }
    
  };
  
  poplike.twitter = {
    ready: false,
    name: 'twitter',
    varname: 'twttr',
    lang: 'en',
    api: 'https://platform.twitter.com/widgets.js',
    share: 'https://twitter.com/share?lang=%%lang%%&amp;url=%%url%%&amp;text=%%title%%',
    width: 80,
    height: 20,
    
    onlike: function(like, unlike) {
      twttr.events.bind('click', like);
    },
    
    button: function (container,url,title, callback) {
      //twttr.widgets.load(container.get(0));
      twttr.widgets.createShareButton(url, container.get(0), {
        count: 'vertical',
        text: title,
        lang: this.lang
      }).then(function () {
        if ($.isFunction(callback)) callback();
      });
    }
    
  };
  
  poplike.vk = {
    ready: false,
    name: "vk",
    varname: "VK",
    api: '//vk.com/js/api/openapi.js',
    share: 'http://vk.com/share.php?url=%%url%%',
    appid: false,
    width: 70,
    
    afterload: function() {
      VK.init({
        apiId: this.appid,
        onlyWidgets: true
      });
    },
    
    onlike: function(like, unlike) {
      VK.Observer.subscribe('widgets.like.liked',like);
      VK.Observer.subscribe('widgets.like.unliked',unlike);
    },
    
    button: function (container,url,title, callback) {
      VK.Widgets.Like(container.attr("id"), {
        type: "mini",
        width: this.width,
        height: this.height,
        pageUrl: url,
        pageTitle: title
      });
      
      if ($.isFunction(callback)) callback();
    }
    
  };
  
  poplike.ok = {
    ready: false,
    name: 'ok',
    varname: 'OK',
    api: 'http://connect.ok.ru/connect.js',
    share: 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=%%url%%&st.comments=%%title%%',
    width: 80,
    height: 70,
    
    onlike: function(like, unlike) {
      if (window.addEventListener) {
        window.addEventListener('message', like);
      } else {
        window.attachEvent('onmessage', like);
      }
    },
    
    button: function(container, url, title, callback) {
    OK.CONNECT.insertShareWidget(container.attr("id"),url, "{width:80,height:50,st:'oval',sz:12,ck:3,vt:'1'}");
      if ($.isFunction(callback)) callback();
    }
    
  };
  
  $.fn.poplike = function(options) {
    options = $.extend({
      network: "facebook",
      url: location.href,
      title: '',
      placement: 'auto right',
      popover_title: 'Lake'
      lang: 'en'
    }, options);
    
    if (options.lang != 'en') {
      poplike.twitter.lang = options.lang;
      poplike.facebook.lang = options.lang + '_' + options.lang.toUpperCase();
    }
    
    return this.one("mouseenter.load", function() {
      var like = poplike.getman();
      like.load(like[like.getvalue(this, options.network)], $.noop);
    })
    .popover({
      container: 'body',
      html:true,
      placement: options.placement,
      trigger: 'hover click focus',
      title: options.popover_title + ' <button type="button" class="close" aria-label="%%close%%"><span aria-hidden="true">&times;</span></button>',
      content: function() {
        var self = $(this);
        var like = poplike.getman();
        var url = like.getvalue(self, options.url);
        var title = like.getvalue(self, options.title);
        var netname = like.getvalue(self, options.network);
        var result = like.getcache(url, netname);
        if (result) return result;
        
        //no cached, need to load
        return like.newbutton(netname, url, title);
      }
    })
    .on("hidden.bs.popover", function() {
      var self = $(this);
      var tip = self.data("bs.popover").tip();
      tip.find(".close").off("click.close");
      var button = tip.find(".poplike-button");
      var like = poplike.getman();
      like.addcache(button.attr("data-url"), button.attr("data-net"), button);
    })
    .on("shown.bs.popover", function() {
      var self = $(this);
      var tip = self.data("bs.popover").tip();
      tip.find(".close").on("click.close", function() {
        self.popover("hide");
        return false;
      });
      
      var button = tip.find(".poplike-button");
      if (button.attr("data-status") == "new") {
        button.attr("data-status", "loading");
        var like = poplike.getman();
        var net = like[like.getvalue(self, options.network)];
        like.load(net, function() {
          net.button(button, button.attr("data-url"), button.attr("data-title"), function() {
            button.attr("data-status", "ready");
          });
        });
      }
      
    });
    
  };
  
})( jQuery, window);