/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( window ){
  window.litepubl.tml.fileman = {
    item: '<div class="file-item">' +
    '<div class="file-toolbar">' +
    '<a href="#" title="%%lang.add%%" class="add-toolbutton"></a>' +
    '<a href="#" title="%%lang.del%%" class="delete-toolbutton"></a>' +
    '<a href="#" title="%%lang.property%%" class="property-toolbutton"></a>' +
    '</div>' +
    '<div class="file-content">' +
    '%%content%%' +
    '</div>' +
    '</div>',
    
    image: '<a rel="prettyPhoto[gallery-fileman]" href="%%link%%" class="file-image"><img src="%%previewlink%%" title="%%title%%" alt="%%description%%" border="0" /></a>',
    
    file: '<p>' +
    '%%lang.file%%: <a href="%%link%%" title="%%title%%">%%description%%</a><br />' +
    '%%lang.filesize%%: <span class="text-right">%%size%%</span><br />' +
    '%%lang.title%%: %%title%%<br />' +
    '%%lang.description%%: %%description%%<br />' +
    '%%lang.keywords%%: %%keywords%%<br />' +
    '</p>',
    
    /*
    tabs: '<div id="uploader"></div>' +
    '<div id="posteditor-files-tabs">' +
    '<ul>' +
    '<li><a href="#current-files"><span>%%lang.currentfiles%%</span></a></li>' +
    '<li><a href="#new-files"><span>%%lang.newupload%%</span></a></li>' +
    '</ul>' +
    '<div id="current-files">' +
    '<div class="file-items"></div>' +
    '<div class="file-items-after"></div>' +
    '</div>' +
    
    '<div id="new-files" class="files-tab">' +
    '<div class="file-items"></div>' +
    '<div class="file-items-after"></div>' +
    '</div>' +
    
    '</div>' +
    '<p class="hidden"><input type="hidden" name="files" value="" /></p>',
    */
    
    tab: '<div class="files-tab" id="filepage-%%index%%">' +
    '<div class="file-items"></div>' +
    '<div class="file-items-after"></div>' +
    '</div>',
    
    tabli: '<li><a href="#filepage-%%index%%">%%index%%</a></li>',
    
    fileprops: '<div class="form-group"><label for="fileprop-title">%%lang.title%%</label>' +
    '<input type="text" class="form-control" name="fileprop-title" id="text-fileprop-title" value="" /></div>' +
    
    '<div class="form-group"><label for="text-fileprop-description">%%lang.description%%</label>' +
    '<input type="text" class="form-control" name="fileprop-description" id="text-fileprop-description" value="" /></div>' +
    
    '<div class="form-group"><label for="text-fileprop-keywords">%%lang.keywords%%</label>' +
    '<input type="text" class="form-control" name="fileprop-keywords" id="text-fileprop-keywords" value="" />'
  };
  
})( window);