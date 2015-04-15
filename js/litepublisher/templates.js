/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function($, litepubl){
  $.extend(litepubl.tml, {
    text:  '<div class="form-group"><label for="text-%%name%%">%%title%%</label>' +
    '<input type="text" class="form-control" name="%%name%%" id="text-%%name%%" value="%%value%%" /></div>',
    
    radio: '<div class="radio"><label><input type="radio" name="%%name%%" id="radio_%%name%%_%%value%%" value="%%value%%" />%%title%%</label></div>',
    
    getedit: function(title, name, value) {
      return $.simpletml(litepubl.tml.text, {
        name: name,
        value: value ? value : '',
        title: title
      });
    }
    
  });
  
}(jQuery, litepubl));