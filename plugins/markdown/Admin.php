<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

namespace litepubl\plugins\markdown;

class Admin extends \litepubl\admin\Panel
{

  public function getcontent() {  
$plugin = Plugin::i();
    $lang = $this->getLangAbout();
    $args = $this->args;
      $args->formtitle = $lang->name;
      $args->deletep = $plugin->deletep;
return $this->admin->form('[checkbox=deletep]', $args);
}

  public function processform() {
$plugin = Plugin::i();
$plugin->deletep = isset($_POST['deletep']);
$plugin->save();
}

}