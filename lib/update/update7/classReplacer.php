<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\update;

class classReplacer
{

    public $classmap;

    public function __construct()
    {
        $this->classmap = include __DIR__ . '/classmap.php';
    }

    public function file($filename)
    {
        $s = file_get_contents($filename);
        $s = $this->replace($s);
        ffile_put_contents($filename, $s);
    }

    public function regexpReplace($s)
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
        if (strpos($s, "namespace $ns;")) {
            return $s;
        }
        
        $uns = "use $new;";
        if (strpos($s, $uns)) { return $s; 
        }
        
        $i = strpos($s, "\n\n", strpos($s, 'namespace '));
        if (! $i) {
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
                    // exit();
                }
            }
        }
        
        $list->close();
    }

    public function replace(string $s): string
    {
        $result = '';
        $uses = [];
        
        $a = token_get_all($s);
        foreach ($a as $i => $t) {
            if (count($t) > 1) {
                if ($t[0] == \T_STRING) {
                    $v = $t[1];
                    if (isset($this->map[$v])) {
                        $v = $this->map[$v];
                        $uses[] = $v;
                        $v = substr($v, strrpos($v, '\\') + 1);
                    }
                    
                    $result .= $v;
                } else {
                    $result .= $t[1];
                }
            } else {
                $result .= $t;
            }
        }
        
        if (count($uses)) {
            $result = $this->insertuse($result, $uses);
        }
        
        return $result;
    }

    public function insertUse(string $s, array $uses)
    {
        $uses = array_unique($uses);
        foreach ($uses as $class) {
            $use = "use $class;";
            if (strpos($s, $use)) {
                continue;
            }
            
            $ns = substr($class, 0, strrpos($class, '\\'));
            if (strpos($s, "namespace $ns;")) {
                continue;
            }
            
            $i = strpos($s, "\n\n", strpos($s, 'namespace '));
            if (! $i) {
                echo "Cant insert $use<br>";
                return $s;
            }
            
            $s = substr($s, 0, $i) . "\n" . $use . substr($s, $i);
        }
        
        return $s;
    }
}
