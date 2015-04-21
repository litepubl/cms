/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  litepubl.Storage = Class.extend({
    local: false,
    session: false,
    global: false,
    link: false,
    png: false,
    enabled: false,
    
    init: function() {
      var k = 'litepubl_test';
      var v = 'testvalue';
      
      if("localStorage" in window){
        try {
          window.localStorage.setItem(k, v);
          window.localStorage.removeItem(k);
          this.local = true;
      } catch(e) {}
      }
      
      if("sessionStorage" in window){
        try {
          window.sessionStorage.setItem(k, v);
          window.sessionStorage.removeItem(k);
          this.session = true;
      } catch(e) {}
      }
      
      if("globalStorage" in window){
        try {
          if(window.globalStorage) {
            var storage = window.globalStorage[window.location.hostname == "localhost" ? "localhost.localdomain" : window.location.hostname];
            storage.setItem(k, v);
            storage .removeItem(k);
            this.global = storage;
          }
      } catch(e) {}
      }
      
      try{
        var link = document.createElement("link");
        if(link.addBehavior){
          //link.style.behavior = "url(#default#userData)";
          link.addBehavior("#default#userData");
          document.getElementsByTagName("head")[0].appendChild(link);
          link.load();
          this.link = link;
        }
    } catch( e ) {}
      
      try {
        var canvas = document.createElement('canvas');
        if (canvas.getContext) this.png = true;
    } catch( e ) {}
      
      
      this.enabled = this.local || this.session || !!this.global || !!this.link;
    },
    
    get: function(name) {
      var result = false;
      if (this.local) {
        if (result = window.localStorage.getItem(name)) return result;
      }
      
      if (this.session) {
        if (result = window.sessionStorage.getItem(name)) return result;
      }
      
      if (this.global) {
        if (result = this.global.getItem(name)) return result;
      }
      
      if (this.link) {
        this.link.load();
        if (result = this.link.getAttribute( name)) return result;
      }
      
      return false;
    },
    
    set: function(name, value, single) {
      if (single) {
        if (this.local) {
          window.localStorage.setItem(name, value);
        } else if (this.session) {
          window.sessionStorage.setItem(name, value);
        } else if (this.global) {
          this.global.setItem(name, value);
        } else if (this.link) {
          this.link.setAttribute( name, value );
          this.link.save();
        } else {
          return false;
        }
        return true;
      }
      
      if (this.local) window.localStorage.setItem(name, value);
      if (this.session) window.sessionStorage.setItem(name, value);
      if (this.global) this.global.setItem(name, value);
      if (this.link) {
        this.link.setAttribute( name, value );
        this.link.save();
      }
      
      return this.enabled;
    },
    
    remove: function(nam) {
      if (this.local) window.localStorage.removeItem(name);
      if (this.session) window.sessionStorage.removeItem(name);
      if (this.global) this.global.removeItem(name);
      if (this.link) {
        this.link.removeAttribute( name);
        this.link.save();
      }
      
      return this.enabled;
    }
    
  });//storage
  
  litepubl.DataStorage = Class.extend({
    
    init: function() {
      litepubl.getstorage();
    },
    
    get: function(name) {
      var result = litepubl.storage.get(name);
      if (!result) result = $.cookie(name);
      if (result) return $.parseJSON(result);
      return false;
    },
    
    set: function(name, data) {
      var value = $.toJSON(data);
      if (!litepubl.storage.set(name, value, true)) {
        $.cookie(name, value, {
          path: '/',
          expires: 3650
        });
      }
    },
    
    remove: function(name) {
      if (!litepubl.storage.remove(name)) $.cookie(name, false);
    }
    
  });
  
  litepubl.getstorage = function() {
    if ("storage" in litepubl) return litepubl.storage;
    return litepubl.storage = new litepubl.Storage();
  };
  
}(jQuery, document, window));