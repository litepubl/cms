<?php

namespace litepubl;

$cl = litepubl::$classes;
foreach ($cl->items as $classname => $item) {
if (!strpos($classname, '\\') {
unset($cl->items[$classname]);
$cl->items['litepubl\\' . $classname] = $item;
}
}

$cl->save();

$m = tmenus::i();
foreach ($m->items as $id => $item) {
if (!strpos($item['class'], '\\')) {
$m->items[$id]['class'] = 'litepubl\\' . $item['class'];
}
}
$m->save();

//views
//widgets
//xmlrpx
//jsonserver

$pl = tplugins::i();
foreach ($pl->items as $name => $item) {
if (!strpos($item['classname'], '\\')) {
$item['classname'] = 'litepubl\\' . $item['classname'];
}

if ($item['adminclassname'] && !strpos($item['classname'], '\\')) {
$item['adminclassname'] = 'litepubl\\' . $item['adminclassname'];
}

$pl->items[$name] = $item;
}
$pl->save();

//posts table

//shops: deliveries, paymethods