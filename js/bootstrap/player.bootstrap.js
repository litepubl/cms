/**
* Lite Publisher
* Copyright (C) 2010, 2015 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function($, document){
  'use strict';
  
  litepubl.Bootstrapplayer= litepubl.Mediaplayer.extend({
    video_tml: '<video src="%%siteurl%%/files/%%file.filename%%" type="%%file.mime%%" controls="controls" autoplay="autoplay"></video>',
    clicked: false,

    video: function(links) {
      var self = this;
      links.on("click.playvideo", function(event) {
        event.preventDefault();
        self.clicked = $(this);
      });
      
      this.tmplink.prettyPhoto({
        custom_markup: tml.pretty,
        default_width: this.width,
        default_height: this.height,
        opacity: 0.80, /* Value between 0 and 1 */
        modal: true, /* If set to true, only the close button will close the window */
        deeplinking: false, /* Allow prettyPhoto to update the url to enable deeplinking. */
        keyboard_shortcuts: false, /* Set to false if you open forms inside prettyPhoto */
        show_title: false, /* true/false */
        social_tools: false,
        //hideflash: true,
        
        /* Called when prettyPhoto is closed */
        callback: function(){
          $(document).off('keydown.onEscape');
          self.clicked = false;
        },
        
        changepicturecallback: function(){
          $.onEscape($.prettyPhoto.close);
          self.ready(function() {
            self.mediaelement();
          });
        }
      });
    },
    
    mediaelement: function() {
      var tml = this.tml;
      var html = $.simpletml(tml.video, {
        file: this.clicked.data("file"),
        siteurl: ltoptions.files
      });
      
      $(html).appendTo(tml.holder).videoplayer({
      });//mediaelement
    }
    
  });//class
  
  $(document).ready(function() {
    litepubl.mediaplayer = new litepubl.Bootstrapplayer($("audio"), $(".videofile"));
  });
})( jQuery, document);