<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\admin\service;

use litepubl\debug\LogException;
use litepubl\view\Args;
use litepubl\view\Lang;

class Run extends \litepubl\admin\Menu
{
    public function getContent(): string
    {
        $args = new Args();
        $lang = Lang::admin('service');
        $args->formtitle = $lang->runhead;
        $args->content = isset($_POST['content']) ? $_POST['content'] : '';
        return $this->admintheme->form(
            '
[editor=content]
', $args
        );
    }

    public function processForm()
    {
        try {
            $result = eval($_POST['content']);
            return sprintf('<pre>%s</pre>', $result);
        } catch (\Throwable $e) {
            return sprintf('<pre>%s</pre>', LogException::getLog($e));
        }
    }
}
