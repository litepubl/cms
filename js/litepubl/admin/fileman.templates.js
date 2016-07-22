/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
  */

(function(window) {
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
      '<li><a href="%%link%%" title="%%title%%">%%description%%</a></li>' +
      '<li><span class="text-right">%%size%%</span></li>' +
      '</ul>',

    tab: '<div class="files-tab file-items" data-page="%%page%%" data-status="empty"></div>'
  };

})(window);