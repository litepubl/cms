/**
* Lite Publisher
* Copyright (C) 2010, 2015 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function( $){
  'use strict';
  
  litepubl.Mediaplayer= Class.extend({
    width: 450,
    height: 300,
    script: false,

    init: function(audio, video) {
      this.width = ltoptions.video_width;
      this.height = ltoptions.video_height;
      
      if (audio.length) {
        var self = this;
        this.load(function() {
          self.audio(audio);
        });
      }
      
      if (video.length) this.video(video);
    },

    load: function(callback) {
      if (this.script) return this.script.done(callback);

        $.load_css(ltoptions.files + "/js/mediaelement/css/mediaelementplayer.min.css");
        this.script = $.load_script(ltoptions.files + "/js/mediaelement/videoplayer.min.js", callback);
    },

player: function(elem, options) {
return elem.mediaelementplayer($.extend(
options ? options : {}, 
{
 features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
pluginPath: ltoptions.files + "/js/mediaelement/"
},
"mediaplayer" in lang ? lang.mediaplayer : {}
));
},
    
    audio: function(links) {
      return this.player(links, {
        audioWidth: 400,
        audioHeight: 30,
        startVolume: 1
      });
    },
    
    video: function(links) {
}

});

})( jQuery);