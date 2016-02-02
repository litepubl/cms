(function( litepubl){
  'use strict';

litepubl.tml.ui = litepubl.tml.ui || {};
litepubl.tml.ui.tabs = {
tabs: '<div class="admintabs"><ul class="nav nav-tabs" role="tablist">%%head%%</ul>' +
    '<div class="tab-content">%%tab%%</div></div>',
head: '<li role="presentation"><a href="#tabpanel-%%id%%" aria-controls="tabpanel-%%id%%" role="tab" data-toggle="tab">%%title%%</a></li>',
tab: '<div role="tabpanel" class="tab-pane fade" id="tabpanel-%%id%%"></div>',
spinner: '<div class="text-center"><span class="fa fa-spin fa-spinner"></span></div>'
//fa-circle-o-notch, fa-refresh 
};
})( litepubl);