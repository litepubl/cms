<?php

namespace litepubl\debug;

$dir = dirname(__DIR__) . '/core/';
foreach (array(
'Paths.php',
'Singleton.php',
//'debugproxy.class.php');
'Data.php',
'Array2prop.php',
'AppTrait.php',
'Storage.php',
'StorageInc.php',
//'storagejson.php',
'StorageMemcache.php',

//old storages
'PoolStorage.php',
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
    require_once($dir . $filename);
}