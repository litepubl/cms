
namespace litepubl;<?php

function update615() {
$cl = litepubl::$classes;
unset($cl->data['factories']);
unset($cl->data['classes']);
$cl->kernel = [];

unset($cl->items['tini2array']);
unset($cl->items['inifiles']);
unset($cl->items['tabstractpingbacks']);
unset($cl->items['tautoform']);
unset($cl->items['adminitems']);

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