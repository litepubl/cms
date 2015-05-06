/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( window ){
  window.litepubl.tml.fileman = {
    item: '<div class="file-item" data-idfile="%%id%%">' +
'%%toolbar%%' +
    '<div class="file-content">' +
    '%%content%%' +
    '</div>' +
    '</div>',

    toolbar: '<div class="file-toolbar btn-toolbar" role="toolbar" aria-label="%%lang.filebuttons%%">' +
    '<button type="button" title="%%lang.del%%" class="delete-toolbutton tooltip-toggle btn btn-default"><span class="fa fa-remove" aria-hidden="true"></span> <span class="sr-only">%%lang.del%%</span></button>' +
    '<button type="button" title="%%lang.property%%" class="property-toolbutton tooltip-toggle btn btn-default"><span class="fa fa-edit" aria-hidden="true"></span> <span class="sr-only">%%lang.property%%</span></button>' +
    '</div>',
    
    image: '<a href="%%link%%" class="file-image"><img src="%%previewlink%%" title="%%title%%" alt="%%description%%" /></a>',
    
    file: '<ul>' +
    '<li>%%lang.file%%: <a href="%%link%%" title="%%title%%">%%description%%</a></li>' +
    '<li>%%lang.filesize%%: <span class="text-right">%%size%%</span></li>' +
    '<li>%%lang.title%%: %%title%%</li>' +
    '<li>%%lang.description%%: %%description%%</li>' +
    //'<li>%%lang.keywords%%: %%keywords%%</li>' +
    '</ul>',
    
    tabs: '<p>%%lang.helpselfiles%%</p>' +
    '<div id="posteditor-files-tabs">' +
    '<ul>%%head%%</ul>' +
    '%%body%%' +
    '</div>',
    
    tabhead : '<li><a href="#filepage-%%index%%">%%index%%</a></li>',
    tab: '<div class="files-tab" id="filepage-%%index%%">' +
    '<div class="file-items" data-page="%%index%%" data-status="empty"></div>' +
    '</div>'
  };
  
})( window);