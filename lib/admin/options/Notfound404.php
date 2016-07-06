<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\options;

use litepubl\pages\Notfound404 as Page404;
use litepubl\view\Args;
use litepubl\view\Lang;

class Notfound404 extends \litepubl\admin\Menu
{
    public function getContent(): string
    {
        $page404 = Page404::i();
        $args = new Args();
        $args->notify = $page404->notify;
        $args->text = $page404->text;

        $lang = Lang::admin('options');
        $args->formtitle = $lang->edit404;
        return $this->admintheme->form(
            '
[checkbox=notify]
[editor=text]
', $args
        );
    }

    public function processForm()
    {
        $page404 = Page404::i();
        $page404->notify = isset($_POST['notify']);
        $page404->text = trim($_POST['text']);
        $page404->save();
    }
}
