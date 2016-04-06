<?php
function update613() {
    tsite::i()->jquery_version = '1.12.3';

if (class_exists('tclasses', false)) {
$cl = tclasses::i();
} else {
$cl = litepubl\tclasses::i();
}

$ini = parse_ini_file(dirname(__DIR__) . '/install/ini/kernel.ini', false);
foreach ($ini as $k => $v) {
$ini[$k] = 'lib/' . $v;
}
$cl->data['kernel'] = $ini;

$cl->items['tcomment'] = 'lib/comments.php';
$cl->save();
}