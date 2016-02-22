<?php
function update607() {
  litepublisher::$site->jquery_version = '1.12.1';
litepublisher::$site->save();

$classes = litepublisher::$classes;
unset($classes->items['poststatus']);
unset($classes->items['tadminthemes']);
unset($classes->items['tableprop']);
$classes->items['datefilter'] = array('kernel.admin.php', '', 'filter.datetime.class.php');
$classes->items['ulist'] = array('kernel.admin.php', '', 'html.ulist.class.php');
$classes->items['adminsecure'] = array('admin.options.secure.class.php', '');
$classes->items['getter'] = array('kernel.php', '', 'getter.class.php');

unset($classes->items['tuitabs']);
$classes->items['tabs'] = array('kernel.admin.php', '', 'html.tabs.class.php');

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


  $lm = tlocalmerger::i();
$lm->lock();
$lm->add('admin', 'plugins/bootstrap-theme/resource/' . litepublisher::$options->language . '.admin.ini');
$lm->deletehtml('lib/languages/posteditor.ini');
$lm->deletehtml('plugins/downloaditem/resource/html.ini');
$lm->deletehtml('plugins/tickets/resource/html.ini');
if ($classes->exists('tticket')) {
$lm->add('admin', 'plugins/tickets/resource/' . litepublisher::$options->language . '.admin.ini');
}
$lm->unlock();

    $js = tjsmerger::i();
    $js->lock();

$ajax = tajaxposteditor::i();
if ($ajax->visual) {
    $js->deletefile('posteditor', $ajax->visual);
    $js->deletetext('posteditor', 'visual');
}

//replace ui tabs
include_once(litepublisher::$paths->lib . 'install/jsmerger.class.install.php');
tjsmerger_ui_admin($js, false);
tjsmerger_bootstrap_admin($js, true);

//ui datepicker adapter
$js->add('admin', 'js/litepubl/ui/datepicker.adapter.min.js');

  $parser = tthemeparser::i();
if (!isset($parser->data['themefiles'])) {
$parser->data['themefiles'] = array();
  $parser->save();
}

$js->unlock();

$css->deletefile('admin', '/js/jquery/ui/redmond/jquery-ui.min.css');
$css->unlock();

  unset(litepublisher::$site->data['jqueryui_version']);
litepublisher::$site->save();

$aj = tajaxposteditor::i();
if (!isset($aj->data['eventnames'])) {
$aj->data['eventnames'] = array();
$aj->save();
}

if ($classes->exists('tticket')) {
if ($man->column_exists('tickets', 'poll')) {
$man->alter('tickets', 'drop poll');
}
}

}