<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

class classReplacer
{
    public $map;

    public function __construct()
    {
        $this->map = include __DIR__ . '/classmap.php';
    }

    public function file(string $filename)
    {
        if (substr($filename, -4) == '.php') {
                $s = file_get_contents($filename);
                $s = $this->replace($s);
                file_put_contents($filename, $s);
        }
    }

    public function dir($dir)
    {
        $list = dir($dir);
        while ($name = $list->read()) {
            if ($name == '.' || $name == '..' || $name == 'kernel.php') {
                continue;
            }
            
            $filename = $dir . '/' . $name;
            if (is_dir($filename)) {
                $this->dir($filename);
            } else {
                $this->file($filename);
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
