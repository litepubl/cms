
namespace litepubl;<?php

function update615() {
$cl = litepubl::$classes;
$cl->namespaces['litepubl\core'] = 'lib/core';
$cl->namespaces['litepubl\admin'] = 'lib/admin';
$cl->namespaces['litepubl\xmlrpc'] = 'lib/xmlrpc';
$cl->kernel['litepubl\core'] = 'kernel.core.php';

foreach ([
'tdata',
'tevents',
'tcoevents'

'tadminservice',
] as $classname) {
unset($cl->items[$classname]);
unset($cl->kernel[$classname]);
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