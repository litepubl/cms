<?php
function update601() {
litepublisher::$classes->items['memstorage'] = array('kernel.php', '', 'memstorage.class.php');
litepublisher::$classes->save();
}