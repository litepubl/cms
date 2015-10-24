<?php
function update601() {
litepublisher::$classes->items['memstorage'] = array('kernel.php', '', 'storage.mem.class.php');
litepublisher::$classes->items['tfilestorage'][0] = 'storage.file.class.php';
litepublisher::$classes->items['storage'][0] = 'storage.class.php';
litepublisher::$classes->save();
}