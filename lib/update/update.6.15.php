
namespace litepubl;<?php

function update615() {
$cl = litepubl::$classes;
$cl->namespaces['litepubl\core'] = 'lib/core';
$cl->namespaces['litepubl\admin'] = 'lib/admin';
$cl->namespaces['litepubl\xmlrpc'] = 'lib/xmlrpc';
$cl->kernel['litepubl\core'] = 'kernel.core.php';

unset($cl->items['tini2array']);
unset($cl->items['inifiles']);
unset($cl->items['tabstractpingbacks']);
unset($cl->items['tautoform']);

$a = include(__DIR__ . '/classmap.php');
foreach ($a as $oldclass => $newclass) {
unset($cl->items[$oldclass]);
unset($cl->kernel[$oldclass]);
}

$cl->save();

$m = tmenus::i();
$admin = tadminmenus::i();
foreach ([
'tadminservice' => 'litepubl\admin\Service',

] as $oldclass => $newclass) {
            litepubl::$urlmap->db->update('class =' . dbquote($newclass) , 'class = ' . dbquote($oldclass));
$m->renameClass($oldclass, $newclass);
$admin->renameClass($oldclass, $newclass);
}

$m->save();
$admin->save();
}