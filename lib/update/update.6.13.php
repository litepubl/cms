<?php
function update613() {
    tsite::i()->jquery_version = '1.12.3';
$cl = tclasses::i();
$cl->data['kernel']['tplugin'] = 'kernel.php';
$cl->data['kernel']['tclasses'] = 'kernel.php';
$cl->data['kernel']['tdata'] = 'kernel.php';
$cl->data['kernel']['storage'] = 'kernel.php';
$cl->save();
}