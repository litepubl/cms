<?php

namespace litepubl\admin;
use litepubl\view\MainView;
use litepubl\view\Lang;
use litepubl\view\Args;

class Simple implements AdminInterface
{
public $admin;
public $theme;
public $lang;
public $args;

public function __construct() {
$schema = MainView::i()->schema;
$this->admin = $schema->admintheme;
$this->theme = $schema->theme;
$this->lang = Lang::admin();
$this->args = new Args();
}

    public function getcontent() {
}

    public function processform() {
}

}
