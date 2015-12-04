<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class ttablecolumns {
  public $style;
  public $head;
  public $checkboxes;
  public $checkbox_tml;
  public $item;
  public $changed_hidden;
  public $index;
  
  public function __construct() {
    $this->index = 0;
    $this->style = '';
    $this->checkboxes = array();
    $this->checkbox_tml = '<input type="checkbox" name="checkbox-showcolumn-%1$d" value="%1$d" %2$s />
    <label for="checkbox-showcolumn-%1$d"><strong>%3$s</strong></label>';
    $this->head = '';
    $this->body = '';
    $this->changed_hidden = 'changed_hidden';
  }
  
  public function addcolumns(array $columns) {
    foreach ($columns as $column) {
      list($tml, $title, $align, $show) = $column;
      $this->add($tml, $title, $align, $show);
    }
  }
  
  public function add($tml, $title, $align, $show) {
    $class = 'col_' . ++$this->index;
    //if (isset($_POST[$this->changed_hidden])) $show  = isset($_POST["checkbox-showcolumn-$this->index"]);
    $display = $show ? 'block' : 'none';
  $this->style .= ".$class { text-align: $align; display: $display; }\n";
    $this->checkboxes[]=  sprintf($this->checkbox_tml, $this->index, $show ? 'checked="checked"' : '', $title);
    $this->head .= sprintf('<th class="%s">%s</th>', $class, $title);
    $this->body .= sprintf('<td class="%s">%s</td>', $class, $tml);
    return $this->index;
  }
  
  public function build($body, $buttons) {
    $args = new targs();
    $args->style = $this->style;
    $args->checkboxes = implode("\n", $this->checkboxes);
    $args->head = $this->head;
    $args->body = $body;
    $args->buttons = $buttons;
    $tml = tfilestorage::getfile(litepublisher::$paths->languages . 'tablecolumns.ini');
    $theme = ttheme::i();
    return $theme->parsearg($tml, $args);
  }
  
}//class