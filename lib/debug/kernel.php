<?php

namespace litepubl\debug;

$dir = dirname(__DIR__) . '/core/';
foreach ([
'Paths.php',
'Singleton.php',
//'debugproxy.class.php');
'Data.php',
'AppTrait.php',
'Callbacks.php',
'Storage.php',
'StorageInc.php',
//'storagejson.php',
'StorageMemcache.php',
'PoolStorage.php',
'BaseCache.php',
'CacheFile.php',
'CacheMemcache.php',
'Event.php',
'EventsTrait.php',
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
] as $filename) {
    include_once $dir . $filename;
}
