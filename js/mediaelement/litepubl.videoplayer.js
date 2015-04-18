;(function( $){
    "use strict";

$.fn.videoplayer= function(opt) {
return $(this).mediaelementplayer($.extend(opt ? opt : {}, 
{
 features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
pluginPath: ltoptions.files + "/js/mediaelement/"
},
"mediaplayer" in lang ? lang.mediaplayer : {}
));
};
})( jQuery);