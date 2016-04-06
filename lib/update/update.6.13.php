<?php
function update613() {
    tsite::i()->jquery_version = '1.12.3';

if (class_exists('tclasses', false)) {
$cl = tclasses::i();
} else {
$cl = litepubl\tclasses::i();
}

$cl->data['kernel'] = parse_ini_file(dirname(__DIR__) . '/install/ini/kernel.ini', false);
$cl->items['tcomment'] = 'lib/comments.php';
$cl->save();
}