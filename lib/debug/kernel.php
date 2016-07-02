<?php

namespace litepubl\debug;

$dir = dirname(__DIR__) . '/core/';
foreach (array(
'Paths.php',
'Singleton.php',
//'debugproxy.class.php');
'Data.php',
'AppTrait.php',
'Storage.php',
'StorageInc.php',
//'storagejson.php',
'StorageMemcache.php',

//old storages
'PoolStorage.php',
'BaseCache.php',
'CacheFile.php',
'CacheMemcache.php',
   
'Events.php',

'Items.php',
'PoolStorageTrait.php',
'Item.php',
'Classes.php',
'Options.php',
'Site.php',
'Router.php',
'DB.php',
'App.php',
'Arr.php',
'Str.php',
'litepubl.php',
) as $filename) {
    include_once $dir . $filename;
}
