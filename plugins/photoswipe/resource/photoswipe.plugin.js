/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $, litepubl, document){
  'use strict';
  
  litepubl.photoswipePlugin = Class.extend({
    links: false,
    opened: false,
    photoswipe: false,
    holder: false,
  options: {},
    
    init: function(links) {
      if (!links.length) return false;
      
      var self = this;
var lng = lang.photoswipe;
    litepubl.tml.photoswipe = $.parsetml(litepubl.tml.photoswipe, {lang: lng});
      this.options = {
history: true,
        galleryPIDs: true,
        showHideOpacity:true,
        getThumbBoundsFn: false,
        errorMsg: '<div class="pswp__error-msg"><a href="%url%" target="_blank">' + lng.error + '</a></div>',
			shareButtons: this.get_sharebuttons()
      };
      
      this.links = links.on("click.photoswipe", function() {
        self.open($(this));
        return false;
      });
      
    litepubl.openimage = $.proxy(this.openimage, this);
$.ready2($.proxy(this.openhash, this));
    },
    
    open: function(link) {
      if (this.opened) return false;
      this.opened = true;

var items = this.getitems(link.data("idpost"), link.data("file").id);
if (!items.length) {
      this.opened = false;
return false;
}

return this.openitems(items);
},

    openitems: function(items) {
      if (this.opened || !items.length) return false;
      this.opened = true;

      this.holder = $(litepubl.tml.photoswipe).appendTo("body");
      var pswp = this.photoswipe = new PhotoSwipe( this.holder.get(0), PhotoSwipeUI_Default, items, this.options);
      pswp.listen('destroy', $.proxy(this.doclose, this));
      pswp.init();
    },
    
    doclose: function() {
      this.photoswipe = false;
      this.holder.remove();
      this.holder = false;
      this.opened = false;
    },
    
    getitems: function(idpost, idfile) {
      var result = [];
      var options = this.options;
      options.galleryUID = parseInt(idpost);
      
      this.links.each(function(linkindex) {
        var link = $(this);
        if (idpost == link.data("idpost")) {
          var data = link.data("file");
          if (idfile == data.id) {
            options.index = result.length;
          }
          
          result.push({
            src: link.attr("href"),
            //msrc: $("img", link).attr("src"),
            w: parseInt(data.width),
            h: parseInt(data.height),
            title: link.attr("title"),
            pid: parseInt(data.id),
            linkindex: linkindex
          });
        }
      });
      
      return result;
    },

openimage: function(image) {
// save current options for swithing single options
var options = this.options;
this.options = $.extend({
index: 0,
history: false,
galleryPIDs: false,
			shareEl: false,
			counterEl: false,
			arrowEl: false
}, this.options);

this.openitems([{
            src: image.url,
            w: image.width,
            h: image.height,
            title: image.title
          }]);

//return back
this.options = options;
},

get_hash: function() {
        var hash = decodeURI(window.location.hash.substring(1));
        if(hash.length < 5) {
            return false;
        }

var result = {};
        var vars = hash.split('&');
        for (var i = 0; i < vars.length; i++) {
            if(!vars[i]) continue;

            var keys = vars[i].split('=');  
            if(keys.length < 2) continue;

            result[keys[0]] = keys[1];
        }

        return {
            gid: "gid" in result ? parseInt(result.gid, 10) : 0,
            pid: "pid" in result ? parseInt(result.pid, 10) : 0
};
    },

openhash: function() {
var hash = this.get_hash();
if (hash && hash.pid && hash.gid) {
      var items = this.getitems(hash.gid, hash.pid);
      this.openitems(items);
}
},

get_sharebuttons: function() {
var result =
[
				{id:'facebook', 
label:'<span class="fa fa-facebook"> FaceBook',
 url:'https://www.facebook.com/sharer/sharer.php?u={{url}}'
},
				{id:'twitter', 
label:'T<span class="fa fa-twitter"></span> weet',
 url:'https://twitter.com/intent/tweet?text={{text}}&url={{url}}'
},
				{id:'pinterest', label:'Pin it', url:'http://www.pinterest.com/pin/create/button/'+
													'?url={{url}}&media={{image_url}}&description={{text}}'
}

result.push({
id:'download', 
label:'<span class="fa fa-download"></span>' + lng.downlload,
 url:'{{raw_image_url}}',
 download:true
});

return result;
}

  });
  
  $(document).ready(function() {
    litepubl.photoswipe = new litepubl.photoswipePlugin($(".photo"));
  });
  
})( jQuery, litepubl, document );