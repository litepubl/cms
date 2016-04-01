<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl {

class storage {
public $ext;

public function __construct() {
$this->ext = '.php';
}

public function serialize(array $data) {
    return \serialize($data);
}

public function unserialize($str) {
if ($str) {
return \unserialize($str);
}

return false;
}

public function before($str) {
    return \sprintf('<?php /* %s */ ?>', \str_replace('*/', '**//*/', $str));
}

public function after($str) {
return \str_replace('**//*/', '*/', \substr($str, 9, \strlen($str) - 9 - 6));
}

public function getfilename(tdata $obj) {
return litepubl::$paths->data . $obj->getbasename();
}

  public function save(tdata $obj) {
    return $this->savefile($this->getfilename($obj), $this->serialize($obj->data));
  }

  public function savedata($filename, array $data) {
    return $this->savefile($filename, $this->serialize($data));
  }

  public function load(tdata $obj) {
try {
    if ($data = $this->loaddata($this->getfilename($obj))) {
$obj->data = $data + $obj->data;
return true;
    }
    } catch(\Exception $e) {
      echo 'Caught exception: ' . $e->getMessage();
}

    return false;
  }

public function loaddata($filename) {
if ($s = $this->loadfile($filename)) {
return $this->unserialize($s);
}

return false;
}

  public  function loadfile($filename) {
    if (\file_exists($filename . $this->ext) && ($s = \file_get_contents($filename . $this->ext))) {
return $this->after($s);
}

    return false;
  }

  public  function savefile($filename, $content) {
    $tmp = $filename . '.tmp' . $this->ext;
    if (false === \file_put_contents($tmp, $this->before($content))) {
$this->error(\sprintf('Error write to file "%s"', $tmp));
      return false;
    }

    \chmod($tmp, 0666);

//replace file
    $curfile = $filename . $this->ext;
    if (\file_exists($curfile)) {
      $backfile = $filename . '.bak' . $this->ext;
      $this->delete($backfile);
      \rename($curfile, $backfile);
    }

    if (!\rename($tmp, $curfile)) {
$this->error(sprintf('Error rename temp file "%s" to "%s"', $tmp, $curfile));
      return false;
    }

    return true;
  }

  public  function remove($filename) {
$this->delete($filename . $this->ext);
$this->delete($filename . '.bak' . $this->ext);
}

  public  function delete($filename) {
    if (\file_exists($filename) && !\unlink($filename)) {
        \chmod($filename, 0666);
        \unlink($filename);
      }
  }

  public  function error($mesg) {
      litepubl::$options->trace($mesg);
}

}//class

}//namespace