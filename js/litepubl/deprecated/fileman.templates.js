/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 **/

(function(window) {

  var tml = window.litepubl.tml.fileman;
  tml.toolbar = '<div class="file-toolbar">' +
    '<a href="#" title="%%lang.del%%" class="delete-toolbutton"></a>' +
    '<a href="#" title="%%lang.property%%" class="property-toolbutton"></a>' +
    '</div>',

    tml.image = '<a rel="prettyPhoto[gallery-fileman]" href="%%link%%" class="file-image"><img src="%%previewlink%%" title="%%title%%" alt="%%description%%" border="0" /></a>';

  tml.file = '<p>' +
    '%%lang.file%%: <a href="%%link%%" title="%%title%%">%%description%%</a><br />' +
    '%%lang.filesize%%: <span class="text-right">%%size%%</span><br />' +
    '%%lang.title%%: %%title%%<br />' +
    '%%lang.description%%: %%description%%<br />' +
    //'%%lang.keywords%%: %%keywords%%<br />' +
    '</p>';

})(window);