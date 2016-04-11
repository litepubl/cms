<?php

$dir = dirname(__DIR__) . '/core/';
foreach (array(
'Paths.php',
//'debugproxy.class.php');
'data.classDataphp',
'array2prop.class.php',

'Storage.php',
'StorageInc.php',
//'storagejson.php',
'StorageMemcache.php',

//old storages
'DataStorage.php',
'CacheFile.php',
'CacheMemcache.php',
   
'Events.php',

'Items.php',
'DataStorageTrait.php',
'Item.php',
'Classes.php',
'Options.php',
'Site.php',
'Router.php',
'DB.php',

'litepubl.php',
) as $filename) {
    require _once($dir . $filename);
}