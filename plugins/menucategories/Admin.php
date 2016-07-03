<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\menucategories;

use litepubl\core\Plugins;
use litepubl\view\Args;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->formtitle = $lang->formtitle;
        return $this->admin->form($this->admin->getcats($plugin->exitems), $args);
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $plugin->exitems = $this->admin->processCategories();
        $plugin->save();
    }
}
