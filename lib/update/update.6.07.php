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
$classes->items['adminsecure'] = array('admin.options.secure.class.php', '');
$classes->items['getter'] = array('kernel.php', '', 'getter.class.php');
$classes->save();

$m = tadminmenus::i();
$m->lock();
$m->deleteurl('/admin/views/themes/');
$id = $m->url2id('/admin/options/secure/');
$m->items[$id]['class'] = 'adminsecure';
litepublisher::$urlmap->setvalue(litepublisher::$urlmap->urlexists('/admin/options/secure/'),
'class', 'adminsecure');

$m->unlock();

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

$man = tdbmanager::i();
$man->addenum('urlmap', 'type', 'begin');
$man->addenum('urlmap', 'type', 'end');
$man->addenum('urlmap', 'type', 'regexp');
        $man->exec("update {$man->prefix}urlmap set type = 'begin' where type = 'tree'");
$man->delete_enum('urlmap', 'type', 'tree');

litepublisher::$urlmap->data['prefilter'] = litepublisher::$urlmap->db->getitems('type in (\'begin\', \'end\', \'regexp\')');
litepublisher::$urlmap->save();

  tcron::i()->addnightly('turlmap', 'updatefilter', null);
}