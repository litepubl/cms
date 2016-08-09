/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.03
  */

(function(litepubl) {
  'use strict';

  litepubl.tml.ui = litepubl.tml.ui || {};
  litepubl.tml.ui.tabs = {
    tabs: '<div class="admintabs"><ul>%%head%%</ul>%%tab%%</div>',
    head: '<li role="presentation"><a href="#tabpanel-%%id%%" aria-controls="tabpanel-%%id%%" role="tab">%%title%%</a></li>',
    tab: '<div role="tabpanel" id="tabpanel-%%id%%">%%content%%</div>'
  };
})(litepubl);