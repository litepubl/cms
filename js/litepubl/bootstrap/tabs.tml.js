/**
 * Lite Publisher CMS
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function(litepubl) {
  'use strict';

  litepubl.tml.bootstrap = litepubl.tml.bootstrap || {};
  litepubl.tml.bootstrap.tabs = {
    tabs: '<div class="admintabs"><ul class="nav nav-tabs" role="tablist">%%head%%</ul>' +
      '<div class="tab-content">%%tab%%</div></div>',
    head: '<li role="presentation"><a href="#tabpanel-%%id%%" aria-controls="tabpanel-%%id%%" role="tab" data-toggle="tab">%%title%%</a></li>',
    tab: '<div role="tabpanel" class="tab-pane fade" id="tabpanel-%%id%%">%%content%%</div>',
    spin: '<span class="fa fa-spin fa-spinner"></span>'
      //fa-circle-o-notch, fa-refresh 
  };
})(litepubl);