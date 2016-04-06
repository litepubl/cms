<?php

foreach (array(
'utils.functions.php',
'paths.php',
'litepubl.php',
//'debugproxy.class.php');
'data.class.php',
'array2prop.class.php',

'storage.php',
'storageinc.php',
//'storagejson.php',
'storagememcache.php',

//old storages
'datastorage.php',
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
'options.class.php',
'site.class.php',
'urlmap.class.php',
'db.class.php',

'litepubl.init.php',
) as $filename) {
    require (__DIR__ . '/' . $filename);
}