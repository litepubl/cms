<?php

namespace litepubl\update;

use litepubl\updater\ChangeStorage;

class migrate
{
public static $dir = 'data';
public static $storage;

public static function load(string $name): array
{
return static::$storage->loadData(static::$dir . $name);
}

public static function save(string $name, array $data)
{
return static::$storage->saveData(static::$dir . $name, $data);
}

public static function updateJs()
{
$replace = [];
$map = include(__DIR__ . '/pluginsmap.php');
foreach ($map as $old => $new) {
$replace["/$old/"] = "/$new/";
}

$js = static::load('jsmerger');
foreach ($js['items'] as $section => $items) {
foreach ($items['files'] as $i => $filename) {
$items['files'][$i] =strtr($filename, $replace);
}

$js[$section] = $items;
}
static::save('jsmerger', $js);

$css = static::load('cssmerger');
foreach ($css['items'] as $section => $items) {
foreach ($items['files'] as $i => $filename) {
$items['files'][$i] =strtr($filename, $replace);
}

$css[$section] = $items;
}
static::save('cssmerger', $css);
}

public static function updateMenus()
{
$map = [];
$new = include(__DIR__ . '/adminmenu.inc.php');
foreach ($new['items'] as $item) {
$map[$item['url']] = $item['class'];
}

$menus = static::load('adminmenu');
foreach ($menus['items'] as $id => $item) {
$url = $item['url'];
if (isset($map[$url])) {
$item['class'] = $map[$url];
$menus['items'][$id] = $item;
//static::$db->setValue($item['idurl'], 'class', $item['class']);
}
}

static::save('adminmenu', $menus);
}

public static function updateClasses()
{
$data = static::load('storage');
$cl = &$data['classes'];
$cl['namespaces'] = [];
$cl['items'] = [];
unset($cl['factories'], $cl['classes'], $cl['interfaces']);
static::save('storage', $data);

    $xmlrpc = TXMLRPC::i();
    $xmlrpc->deleteclass('twidgets');
}

public static function updatePlugins()
{
    $map = include (__DIR__ . '/pluginsmap.php');
    $plugins = static::load('plugins/index');
    foreach ($plugins['items'] as $name => $item) {
        if (isset($map[$name])) {
            unset($plugins['items'][$name]);
            $plugins['items'][$map[$name]] = $item;
        }
    }
    
static::save('plugins/index', $plugins);
}

public static function updateTables()
{
$db = static::$db;
    $man = dbmanager::i();

    foreach (['posts', 'userpage', 'categories', 'tags', ] as $table) {
        if ($man->columnExists($table, 'idview')) {
            $man->alter($table, "change idview idschema int unsigned NOT NULL default '1'");
        }
    }

$map = include(__DIR__ . '/classmap.php');
$db->table = 'urlmap';
foreach ($map as $old => $new) {
$new = $db->quote($new);
$db->update("class = $new", "class = '$old' or clas = 'litepubl\\\\$old'");
}



}

public static function run()
{
require (__DIR__ . '/eventUpdater.php');
require (dirname(dirname(__DIR__)) . '/updater/ChangeStorage.php');

eventUpdater::$map = include(__DIR__ . '/classmap.php');
$changer = ChangeStorage::create(eventUpdater::getCallback());
$dir = $changer->run('data-6.14');

static::$storage = $changer->dest;
static::$dir = dirname(dirname(dirname(__DIR__))) . '/storage/' . $dir . '/';
static::updateJs();
static::updateMenus();
static::updateClasses();
static::updatePlugins();
static::updateTables();
}

}