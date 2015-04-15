<?php
function update596() {
$t = ttemplate::i();
$t->data['custom'] = array();
$t->save();
}