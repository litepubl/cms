<?php
function update607() {
$classes = litepublisher::$classes;
unset($classes->items['poststatus']);
unset($classes->items['tadminthemes']);
$classes->items['datefilter'] = array('kernel.admin.php', '', 'filter.datetime.class.php');
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
}