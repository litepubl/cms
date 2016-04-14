<?php

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\pages\Notfound404 as Page404;

class Notfound404 extends \litepubl\admin\Menu
{
public function getcontent() {
$page404 = Page404::i();
$args = new Args();
                $args->notify= $page404->notify;
$args->text = $page404->text;

$lang = Lang::admin('options');
$args->formtitle = $lang->edit404;
return $this->admintheme->form('
[checkbox=notify]
[editor=text]
', $args);
}

public function processform() {
$page404 = Page404::i();
$page404->notify = isset($_POST['notify']);
$page404->text = trim($_POST['text']);
$page404->save();
}

}