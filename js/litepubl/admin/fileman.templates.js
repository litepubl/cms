/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( window ){
  window.litepubl.tml.fileman = {
    item: '<div class="file-item">' +
    '<div class="file-toolbar btn-toolbar" role="toolbar" aria-label="%%lang.filebuttons%%">' +
    '<button type="button" title="%%lang.add%%" class="add-toolbutton tooltip-toggle btn btn-default"><span class="fa fa-plus-square" aria-hidden="true"></span> <span class="sr-only">%%lang.add%%</span></button>' +
    '<button type="button" title="%%lang.del%%" class="delete-toolbutton tooltip-toggle btn btn-default"><span class="fa fa-remove" aria-hidden="true"></span> <span class="sr-only">%%lang.del%%</span></button>' +
    '<button type="button" title="%%lang.property%%" class="property-toolbutton tooltip-toggle btn btn-default"><span class="fa fa-edit" aria-hidden="true"></span> <span class="sr-only">%%lang.property%%</span></button>' +
    '</div>' +
    '<div class="file-content">' +
    '%%content%%' +
    '</div>' +
    '</div>',
    
    image: '<a href="%%link%%" class="file-image"><img src="%%previewlink%%" title="%%title%%" alt="%%description%%" /></a>',
    
    file: '<ul>' +
    '<li>%%lang.file%%: <a href="%%link%%" title="%%title%%">%%description%%</a></li>' +
    '<li>%%lang.filesize%%: <span class="text-right">%%size%%</span></li>' +
    '<li>%%lang.title%%: %%title%%</li>' +
    '<li>%%lang.description%%: %%description%%</li>' +
    '<li>%%lang.keywords%%: %%keywords%%</li>' +
    '</ul>',
    
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