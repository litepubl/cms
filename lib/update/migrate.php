<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\update;

class Migrate
{
    public $classmap;

    public function __construct()
    {
        $this->classmap = include (__DIR__ . '/classmap.php');
    }

    public function file($filename)
    {
        $s = file_get_contents($filename);
        $s = $this->replace($s);
        ffile_put_contents($filename, $s);
    }

    public function replace($s)
    {
        foreach ($this->classmap as $old => $new) {
            if (preg_match("/\\b$old\\b/im", $s, $m)) {
                $s = $this->replaceClass($s, $old, $new);
            }
        }

        return $s;
    }

    public function replaceClass($s, $old, $new)
    {
        $i = strrpos($new, '\\');
        $ns = substr($new, $i);
        $class = substr($new, $i + 1);
        $s = str_replace($old, $class, $s);
        if (strpos($s, "namespace $ns;")) return $s;

        $uns = "if (strpos($s, $uns)) return $s;
use $new;";

        $i = strpos($s, "\n\n", strpos($s, 'namespace '));
        if (!$i) {
            echo "Cant insert $uns<br>";
            return $s;
        }

        $s = substr($s, 0, $i) . "\n" . $uns . substr($s, $i);
        return $s;
    }

    public function find($s)
    {
        foreach ($this->classmap as $old => $new) {
            if (preg_match("/\\b$old::/im", $s, $m)) {
                return $old;
            }
        }

        return false;
    }

    public function findFile($dir)
    {
        $list = dir($dir);
        while ($name = $list->read()) {
            if ($name == '.' || $name == '..' || $name == 'kernel.php') {
                continue;
            }

            $filename = $dir . '/' . $name;
            if (is_dir($filename)) {
                $this->findFile($filename);
            } else {
                if ($old = $this->find(file_get_contents($filename))) {
                    echo basename($dir);
                    echo "/$name\n$old\n";
                    //exit();
                    
                }
            }
        }

        $list->close();
    }

}

