<?php
function update607() {
  litepublisher::$site->jquery_version = '1.12.0';
litepublisher::$site->save();

$classes = litepublisher::$classes;
unset($classes->items['poststatus']);
unset($classes->items['tadminthemes']);
unset($classes->items['tableprop']);
$classes->items['datefilter'] = array('kernel.admin.php', '', 'filter.datetime.class.php');
$classes->items['ulist'] = array('kernel.admin.php', '', 'html.ulist.class.php');
$classes->save();

$m = tadminmenus::i();
$m->deleteurl('/admin/views/themes/');

$css = tcssmerger::i();
$css->lock();
$css->replacefile('admin', 
'/js/litepubl/admin/css/calendar.css',
'/js/litepubl/admin/css/calendar.min.css'
);

$css->replacefile('default',
'/js/litepubl/common/css/form-inline.min.css',
'/js/litepubl/common/css/form.inline.min.css'
);

$css->unlock();

$t = ttemplate::i();
$t->footer = str_replace('2015', '2016', $t->footer);
$t->save();
}