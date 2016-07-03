<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\update;

use litepubl\Config;
use litepubl\updater\ChangeStorage;

class migrate
{
    public static $map;
    public static $dir = 'data';
    public static $storage;
    public static $db;

    public static function load(string $name): array
    {
        return static::$storage->loadData(static::$dir . $name);
    }

    public static function save(string $name, array $data)
    {
        return static::$storage->saveData(static::$dir . $name, $data);
    }

    public static function getDB()
    {
        if (class_exists('litepubl\core\DB', false)) {
            return \litepubl\core\DB::i();
        } elseif (class_exists('tdatabase', false)) {
            return \tdatabase::i();
        } elseif (class_exists('litepubl\tdatabase', false)) {
            return \litepubl\tdatabase::i();
        } else {
            $data = static::load('storage');
            $config = $data['options']['dbconfig'];
            // decrypt db password
            $config['password'] = static::decrypt($config['password'], $data['options']['solt'] . '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ');
            
            include_once __DIR__ . '/minidb.php';
            $db = new minidb();
            $db->setconfig($config);
            return $db;
        }
    }

    public static function decrypt($s, $key)
    {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }
        
        $s = mcrypt_decrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
        $len = strlen($s);
        $pad = ord($s[$len - 1]);
        return substr($s, 0, $len - $pad);
    }

    public static function updateJs()
    {
        $replace = [];
        $map = include __DIR__ . '/pluginsmap.php';
        foreach ($map as $old => $new) {
            $replace["/$old/"] = "/$new/";
        }

        $js = static::load('jsmerger');
        foreach ($js['items'] as $section => $items) {
            foreach ($items['files'] as $i => $filename) {
                if (ltrim($filename, '/') == 'js/litepubl/bootstrap/popover.post.min.js') {
                                unset($items['files'][$i]);
                } else {
                                $items['files'][$i] = strtr($filename, $replace);
                }
            }
            
            $js['items'][$section] = $items;
        }
        static::save('jsmerger', $js);

        $css = static::load('cssmerger');
        foreach ($css['items'] as $section => $items) {
            foreach ($items['files'] as $i => $filename) {
                if (ltrim($filename, '/') == 'plugins/regservices/regservices.min.css') {
                                unset($items['files'][$i]);
                } else {
                                $items['files'][$i] = strtr($filename, $replace);
                }
            }
            
            $css['items'][$section] = $items;
        }
        static::save('cssmerger', $css);

        $lm = static::load('localmerger');
        foreach ($lm['items'] as $section => $items) {
            foreach ($items['files'] as $i => $filename) {
                $items['files'][$i] = strtr($filename, $replace);
            }
            
            $lm['items'][$section] = $items;
        }

        static::save('localmerger', $lm);
    }

    public static function updateMenus()
    {
        $map = [];
        $new = include __DIR__ . '/adminmenu.inc.php';
        foreach ($new['items'] as $item) {
            $map[$item['url']] = $item['class'];
        }

        $mapUrl = [
        '/admin/views/addview/' => '/admin/views/addschema/',
        ];
        
        $menus = static::load('adminmenu');
        static::$db->table = 'urlmap';
        foreach ($menus['items'] as $id => $item) {
            $url = $item['url'];
            if (isset($mapUrl[$url])) {
                        $url = $mapUrl[$url];
                        $item['url'] = $url;
                        static::$db->setValue($item['idurl'], 'url', $url);
            }

            if (isset($map[$url])) {
                $item['class'] = $map[$url];
                $menus['items'][$id] = $item;
                 static::$db->setValue($item['idurl'], 'class', $item['class']);
            }
        }
        
        static::save('adminmenu', $menus);
    }

    public static function updateClasses(array $data): array
    {
        $cl = &$data['classes'];
        $cl['namespaces'] = ['litepubl' => 'lib'];
        $cl['items'] = [];
        $cl['kernel'] = [];
        unset($cl['factories'], $cl['classes'], $cl['interfaces']);

        $widgets = $data['widgets'];
        if (isset($widgets['classes']['tpost'])) {
                $widgets['classes']['litepubl\post\Post'] = $widgets['classes']['tpost'];
                unset($widgets['classes']['tpost']);
        }
        $data['widgets'] = $widgets;
        return $data;
    }

    public static function updateOptions(array $data): array
    {
        $data['options']['version'] = '7.00';
        $data['options']['xxxcheck'] = false;
        $data['site']['jquery_version'] = '1.12.4';
        if (empty($data['site']['author'])) {
            $data['site']['author'] = 'Admin';
        }

        unset($data['site']['video_width']);
        unset($data['site']['video_height']);

        return $data;
    }

    public static function updateSchemes(array $data): array
    {
        $items = &$data['views']['items'];
        $a = [
        'tmenus' => 'litepubl\pages\Menus',
        'tadminmenus' => 'litepubl\admin\Menus',
        ];

        foreach ($items as $id => $item) {
                $items[$id]['menuclass'] = $a[$item['menuclass']];
        }

        return $data;
    }

    public static function updateXmlrpc()
    {
         $xmlrpc = static::load('xmlrpc');
        // $xmlrpc->deleteclass('twidgets');
        unset($xmlrpc['items']['litepublisher.getwidget']);
        static::save('xmlrpc', $xmlrpc);
    }

    public static function updatePlugins()
    {
        $map = include __DIR__ . '/pluginsmap.php';
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
        $man = new miniman($db);
        
        foreach ([
            'posts',
            'userpage',
            'categories',
            'tags'
        ] as $table) {
            if ($man->columnExists($table, 'idview')) {
                $man->alter($table, "change idview idschema int unsigned NOT NULL default '1'");
            }
        }
        
        $map = include __DIR__ . '/classmap.php';
        $db->table = 'urlmap';
        foreach ($map as $old => $new) {
            $new = $db->quote($new);
            $db->update("class = $new", "class = '$old' or class = 'litepubl\\\\$old'");
        }
        
        $man->renameEnum('posts', 'class', 'tpost', 'litepubl-post-Post');
        $man->renameEnum('posts', 'class', 'litepubl-tpost', 'litepubl-post-Post');
        
        // $man->renameEnum('posts', 'class', 'product', 'litepubl-product');
    }

    public static function uploadIndex()
    {
        if (class_exists('\tbackuper', false)) {
                $backuper = \tbackuper::i();
        } elseif (class_exists('\litepubl\tbackuper', false)) {
                $backuper = \litepubl\tbackuper::i();
        } elseif (class_exists('\litepubl\updater\Backuper', false)) {
                $backuper = \litepubl\updater\Backuper::i();
        } else {
                echo "Backuper instance not found\n";
                return false;
        }

        //$content = file_get_contents('https://raw.githubusercontent.com/litepubl/cms/master/index.php');
        $content = file_get_contents('D:\OpenServer\domains\cms.cms\index.php');
        $backuper->chdir(dirname(dirname(dirname(__DIR__))));
        $backuper->filer->putcontent('index.php', $content);
    }

    public static function renameDataFolder()
    {
        $storageDir = dirname(dirname(dirname(__DIR__))) . '/storage/';
        rename($storageDir . 'data', $storageDir . 'data-old' . time());
        rename(rtrim(static::$dir, '/'), $storageDir . 'data');
    }

    public static function clearTheme()
    {
        $dir = dirname(dirname(dirname(__DIR__))) . '/storage/data/'; 
        foreach (['themes', 'languages', 'logs'] as $subdir) {
            $list = dir($dir . $subdir);
            while ($filename = $list->read()) {
                if ($filename != '.' && $filename != '..') {
                    unlink($dir . $subdir . '/' . $filename);
                }
            }

            $list->close();
        }
    }

    public static function saveJs()
    {
        define('litepubl\mode', 'config');
        include_once dirname(dirname(dirname(__DIR__))) . '/index.php';
        Config::$debug = true;
        Config::$useKernel = false;
        Config::$ignoreRequest = true;
        include_once dirname(dirname(__DIR__)) . '/debug/kernel.php';
        \litepubl\view\Js::i()->save();
        \litepubl\view\Css::i()->save();
        \litepubl\core\litepubl::$app->poolStorage->commit();
        $contact = \litepubl\pages\Contacts::i();
        $contact->externalFunc($contact, 'update', null);

        \litepubl\core\litepubl::$app->cache->clear();
    }

    public static function run()
    {
        include_once __DIR__ . '/eventUpdater.php';
        include_once __DIR__ . '/backuper.php';
        include_once __DIR__ . '/miniman.php';
        include_once dirname(dirname(__DIR__)) . '/updater/ChangeStorage.php';

        static::clearTheme();
        eventUpdater::$map = include __DIR__ . '/classmap.php';
        $changer = ChangeStorage::create(eventUpdater::getCallback());
        if (0) {
                static::saveJs();
                echo 'ok';
                return;
        }
        $dir = $changer->run('data');
        static::$dir = dirname(dirname(dirname(__DIR__))) . '/storage/' . $dir . '/';
        static::$storage = $changer->dest;
        static::$db = static::getDB();
        $man = new miniman(static::$db);
        backuper::create($man->export());
        
                static::updateJs();
        static::updateMenus();

        $storage = static::load('storage');
        $storage= static::updateClasses($storage);
        $storage = static::updateOptions($storage);
        $storage = static::updateSchemes($storage);
                static::save('storage', $storage);

        static::updateXmlrpc();
        static::updatePlugins();
        static::updateTables();
        //static::uploadIndex();
        static::renameDataFolder();
        echo 'data migrated<br>';
        static::saveJs();
        echo 'migrate completed';
    }

}
