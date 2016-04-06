<?php
function update611() {
if (class_exists('tclasses', false)) {
$cl = tclasses::i();
} else {
$cl = litepubl\tclasses::i();
}

if (!isset($cl->data['namespaces'])) {
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
$cl->items['storagejson'] = array('storage.json.php', '', 'storagejson.php');
$cl->items['storagememcache'] = array('kernel.php', '', 'storagememcache.php');
$cl->items['datastorage'] = array('kernel.php', '', 'datastorage.php');
$cl->items['tadminmoderator'][0] = 'adminmoderator.php';
$cl->items['tcomments'] = array('kernel.comments.php', '', 'comments.php');
$cl->items['tcomment'][0] = 'kernel.comments.php';
$cl->items['tcommentmanager'][0] = 'kernel.comments.php';
$cl->items['tcommentform'][0] = 'kernel.comments.php';
$cl->items['tsubscribers'][0] = 'kernel.comments.php';
$cl->items['trssholdcomments'][0] = 'rssholdcomments.php';
$cl->items['tpingbacks'][0] = 'pingbacks.php';

$cl->data['namespaces'] = array();

foreach ($cl->data['interfaces'] as $name => $filename) {
$cl->items[$name] = array($filename, '');
}

unset($cl->data['interfaces']);

$cl->items['itemplate'] = array('kernel.php', '', 'itemplate.php');
$cl->data['kernel'] = array();
foreach ($cl->items as $name => $item) {
$dir = (empty($item[1]) ? 'lib/': 'plugins/' . $item[1] . '/');
if (count($item) == 2) {
$cl->items[$name] = $dir . $item[0];
} else {
$cl->items[$name] = $dir . $item[2];
$cl->data['kernel'][$name] = $dir . $item[0];
}
}

$cl->data['kernel']['tplugin'] = 'kernel.php';
$cl->data['kernel']['tclasses'] = 'kernel.php';
$cl->data['kernel']['tdata'] = 'kernel.php';
$cl->save();

$cl->save();

litepubl::$options->savemodified();
}

$man = tdbmanager::i();
$man->renameEnumValue('posts', 'class', 'tpost', 'litepubl-tpost');
$man->renameEnumValue('posts', 'class', 'tticket', 'litepubl-tticket');
$man->renameEnumValue('posts', 'class', 'tdownloaditem', 'litepubl-tdownloaditem');
$man->renameEnumValue('posts', 'class', 'product', 'litepubl-product');


if (!class_exists('tfiler', false)) {
class_alias('litepubl\tfiler', 'tfiler');
}
}