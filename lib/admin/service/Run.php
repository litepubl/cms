<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\service;
use litepubl\view\Lang;
use litepubl\view\Args;

class Run extends \litepubl\admin\Menu
{
public function getContent() {
$args = new Args();
$lang = Lang::admin('service');
                $args->formtitle = $lang->runhead;
                $args->content = isset($_POST['content']) ? $_POST['content'] : '';
return $this->admintheme->form('
[editor=content]
', $args);
}

public function processForm() {
try {
                $result = eval($_POST['content']);
                return sprintf('<pre>%s</pre>', $result);
} catch (\Exception $e) {
return sprintf('<pre>%s</pre>',  $this->getApp()->options->handexception($e));
}
}

}