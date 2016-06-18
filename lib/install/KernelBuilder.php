<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\install;

class KernelBuilder
{

    public static function buildAll()
    {
        $dir = dirname(__DIR__);
        $list = dir($dir);

        while ($filename = $list->read()) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            if (is_dir($dir . '/' . $filename)) {
                static ::build($dir . '/' . $filename . '/');
            }
        }

        $list->close();
    }

    public static function build($dir)
    {
        $result = '';
        $rules = static ::getRules($dir);
        if ($rules === false) {
            return false;
        }

        $dirlist = dir($dir);
        while ($filename = $dirlist->read()) {
            if ((substr($filename, -4) != '.php') || ($filename == 'kernel.php')) {
                continue;
            }

            if (!in_array($filename, $rules['ignore']) && !in_array($filename, $rules['include'])) {
                $result.= "//$filename\n";
                //$s = trim(substr($s, strpos($s, '*/') + 2));
                $result.= static ::getFile($dir . $filename);
            }
        }
        $dirlist->close();

        $homedir = dirname(dirname(__DIR__)) . '/';
        foreach ($rules['include'] as $filename) {
            $result.= "//$filename\n";
            if (strpos($filename, '/')) {
                $result.= static ::getfile($homedir . $filename);
            } else {
                $result.= static ::getFile($dir . $filename);
            }
        }

        $result = "<?php\n" . $result;
        file_put_contents($dir . 'kernel.php', $result);
    }

    public static function getRules($dir)
    {
        if (!file_exists($dir . 'install/kernel.txt')) {
            return false;
        }

        $s = file_get_contents($dir . 'install/kernel.txt');
        if (!$s) {
            return false;
        }

        $a = explode("\n", $s);

        $result = ['ignore' => [], 'include' => [], ];

        foreach ($a as $filename) {
            if (($filename = trim($filename)) && ($filename[0] != '#') && ($filename[0] != ';')) {
                if ($filename[0] == '!') {
                    $result['ignore'][] = substr($filename, 1);
                } else {
                    $result['include'][] = $filename;
                }
            }
        }

        return $result;
    }

    public static function getFile($filename)
    {
        //return php_strip_whitespace($filename);
        $s = file_get_contents($filename);
        $s = trim(substr($s, 5));
        if (!strpos($filename, 'vendor') && (substr($s, 0, 2) == '/*')) {
            $s = trim(substr($s, strpos($s, '*/') + 2));
        }

        $s.= "\n\n";
        return $s;
    }
}
