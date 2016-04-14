<?php

namespace litepubl\admin\service;
use litepubl\view\Lang;
use litepubl\view\Args;

class Run extends \litepubl\admin\Menu
{
public function getcontent() {
$args = new Args();
$lang = Lang::admin('service');
                $args->formtitle = $lang->runhead;
                $args->content = isset($_POST['content']) ? $_POST['content'] : '';
return $this->admintheme->form('
[editor=content]
', $args);
}

public function processform() {
try {
                $result = eval($_POST['content']);
                return sprintf('<pre>%s</pre>', $result);
} catch (\Exception $e) {
return sprintf('<pre>%s</pre>', litepubl::$options->handexception($e));
}
}

}
