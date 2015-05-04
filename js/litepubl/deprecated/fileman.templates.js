/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( window ){
  
  var tml = window.litepubl.tml.fileman;
  tml.item = '<div class="file-item">' +
  '<div class="file-toolbar">' +
  '<a href="#" title="%%lang.add%%" class="add-toolbutton"></a>' +
  '<a href="#" title="%%lang.del%%" class="delete-toolbutton"></a>' +
  '<a href="#" title="%%lang.property%%" class="property-toolbutton"></a>' +
  '</div>' +
  '<div class="file-content">' +
  '%%content%%' +
  '</div>' +
  '</div>';
  
  tml.    image = '<a rel="prettyPhoto[gallery-fileman]" href="%%link%%" class="file-image"><img src="%%previewlink%%" title="%%title%%" alt="%%description%%" border="0" /></a>';
  
  tml.file = '<p>' +
  '%%lang.file%%: <a href="%%link%%" title="%%title%%">%%description%%</a><br />' +
  '%%lang.filesize%%: <span class="text-right">%%size%%</span><br />' +
  '%%lang.title%%: %%title%%<br />' +
  '%%lang.description%%: %%description%%<br />' +
  '%%lang.keywords%%: %%keywords%%<br />' +
  '</p>';
  
})( window);