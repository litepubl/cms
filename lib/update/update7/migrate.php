<?php

namespace litepubl\update;

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
$new = include(__DIR__ . '/adminmenu.php');
foreach ($new as $item) {
$map[$item['url']] = $item['class'];
}

$menus = static::load('adminmenu');
foreach ($menus['items'] as $id => $item) {
$url = $item['url'];
if (isset($map[$url])) {
$item['class'] = $map[$url];
$menus['items][$id] = $item;
static::$db->setValue($item['idurl'], 'class', $item['class']);
}
}

static::save('adminmenu', $menus);
}

public static function updateClasses()
{
$data = static::load(storage');
$cl = &$data['classes'];
$cl['namespaces'] = [];
$cl['items'] = [];
unset($cl['factories'], $cl['classes'], $cl['interfaces']);

static::save(storage', $data);
}

public static function migrate()
{
static::updateJs();
static::updateMenus();
static::updateClasses();
}

}