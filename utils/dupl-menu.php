$m = adminshopmenus::i();
$m->lock();

$list = [];
foreach ($m->items as $id => $item) {
if (isset($list[$item['url']])) {
unset($m->items[$id]);
} else {
$list[$item['url']] = $id;
}
}

$m->sort();
$m->unlock();