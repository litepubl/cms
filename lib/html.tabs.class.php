<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tabs {
public$head;
public $body;
  public $customdata;
public $_admintheme;
  private static $index = 0;

  public function __construct($admintheme = null) {
$this->_admintheme = $admintheme;
    $this->head = array();
    $this->body = array();
    $this->customdata = false;
  }

public function getadmintheme() {
if (!$this->_admintheme) {
$this->_admintheme = admintheme::i();
}

return $this->_admintheme;
}

  public function get() {
return strtr($this->getadmintheme()->templates['tabs'], array(
'$id' => self::$index++,
'$head' => implode("\n", $this->head),
'$tab' => implode("\n", $this->body),
));

    $data = $this->customdata ? sprintf('data-custom="%s"', str_replace('"', '&quot;', json_encode($this->customdata))) : '';
  }

  public function add($title, $content) {
$this->addtab('', $title, $content);
}

  public function ajax($title, $url) {
$this->addtab($url, $title, '');
}

  public function addtab($url, $title, $content) {
$id = self::$index++;
$admintheme = $this->getadmintheme();
$this->head [] = strtr($admintheme->templates['tabs.head'], array(
'$id' => $id,
'$title' => $title,
'$ajax' => $url,
));

$this->body[] = strtr($this->admintheme->templates['tabs.tab'], array(
'$id' => $id
'$content' => $content
));
  }

  public static function gethead() {
    return '<script type="text/javascript">$.inittabs();</script>';
  }

} //class