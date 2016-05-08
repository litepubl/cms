<?php

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

$uns = "use $new;";
if (strpos($s, $uns)) return $s;

$i = strpos($s, "\n\n", strpos($s, 'namespace '));
if (!$i) {
echo "Cant insert $uns<br>";
return$s;
}

$s = substr($s, 0, $i) . "\n" . $uns . substr($s, $i);
return $s;
}

}