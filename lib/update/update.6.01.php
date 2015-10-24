<?php
function update601() {
litepublisher::$classes->items['tfilestorage'] = array('kernel.php', '', 'storage.file.class.php');
litepublisher::$classes->items['tstorage'] = array('kernel.php', '', 'storage.class.php');
litepublisher::$classes->items['memstorage'] = array('kernel.php', '', 'storage.mem.class.php');
unset(litepublisher::$classes->items['tlitememcache']);
unset(litepublisher::$classes->items['tfilecache']);
litepublisher::$classes->items['cachestorage_memcache'] = array('kernel.php', '', 'storage.cache.memcache.class.php');
litepublisher::$classes->items['cachestorage_file'] = array('kernel.php', '', 'storage.cache.file.class.php');
litepublisher::$classes->save();
}