
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

'tadminservice',
] as $classname) {
unset($cl->items[$classname]);
unset($cl->kernel[$classname]);
}

$cl->save();
}