<?php
function update601() {
litepublisher::$classes->items['memstorage'] = array('kernel.php', '', 'storage.mem.class.php');
litepublisher::$classes->save();
}