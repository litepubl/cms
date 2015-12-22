<?php
function update603() {
$classes = litepublisher::$classes;
$classes->items['themevars'] = array('kernel.templates.php', '', 'theme.vars.class.php');
$classes->add('tablebuilder, 'kernel.admin.php', '', 'html.tablebuilder.class.php');
$classes->save();
}