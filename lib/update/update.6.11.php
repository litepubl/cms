<?php
function update611() {
if (class_exists('tclasses', false)) {
$cl = tclasses::i();
} else {
$cl = litepubl\tclasses::i();
}

unset($cl->items['litepubl\litepubl']);
unset($cl->items['litepubl\paths']);
unset($cl->items['litepubl\storage']);
unset($cl->items['litepubl\incstorage']);
unset($cl->items['litepubl\jsonstorage']);
unset($cl->items['litepubl\memcachestorage']);
unset($cl->items['litepubl\datastorage']);

$cl->items['litepubl'] = array('kernel.php', '', 'litepubl.php');
$cl->items['paths'] = array('kernel.php', '', 'paths.php');
$cl->items['storage'] = array('kernel.php', '', 'storage.php');
$cl->items['storageinc'] = array('kernel.php', '', 'storageinc.php');
$cl->items[storagejson'] = array('storage.json.php', '', 'storagejson.php');
$cl->items['storagememcache'] = array('kernel.php', '', 'storagememcache.php');
$cl->items['datastorage'] = array('kernel.php', '', 'datastorage.php');
$cl->items['tadminmoderator'][0] = 'adminmoderator.php';
$cl->items['tcomments'] = array('kernel.comments.php', '', 'comments.php');
$cl->items['tcomment'][0] = 'kernel.comments.php';
$cl->items['tcommentmanager'][0] = 'kernel.comments.php';
$cl->items['tcommentform'][0] = 'kernel.comments.php';
$cl->items['tsubscribers'][0] = kernel.comments.php
$cl->items['trssholdcomments'][0] = 'rssholdcomments.php';
$cl->items['tpingbacks'][0] = 'pingbacks.php';

$cl->data['namespaces'] = array();

foreach ($cl->data['interfaces'] as $name => $filename) {
$cl->items[$name] = array($filename, '');
}

unset($cl->data['interfaces']);
$cl->save();
}