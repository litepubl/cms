<?php

foreach (array(
'paths.php',
'litepubl.php',
//'debugproxy.class.php');
'data.class.php',
'array2prop.class.php',
'utils.functions.php',

'storage.php',
'storage.inc.php',
'storage.json.php',
'storage.memcache.php',

//old storages
'storage.class.php',
'storage.mem.class.php',
'storage.cache.file.class.php',
'storage.cache.memcache.class.php',
   
'events.class.php',
'events.exception.class.php',
'events.coclass.php',
'events.storage.class.php',

'items.class.php',
'items.storage.class.php',
'items.single.class.php',
'item.class.php',
'item.storage.class.php',

'classes.class.php',
'classes.functions.php',
'options.class.php',
'site.class.php',

'litepubl.init.php',
) as $filename) {
    require (__DIR__ . '/' . $filename);
}