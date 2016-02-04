(function(litepubl) {
  'use strict';

  litepubl.tml.ui = litepubl.tml.ui || {};
  litepubl.tml.ui.tabs = {
    tabs: '<div class="admintabs"><ul>%%head%%</ul>%%tab%%</div>',
    head: '<li role="presentation"><a href="#tabpanel-%%id%%" aria-controls="tabpanel-%%id%%" role="tab">%%title%%</a></li>',
    tab: '<div role="tabpanel" id="tabpanel-%%id%%">%%content%%</div>'
  };
})(litepubl);