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


namespace litepubl\plugins\markdown;

class Admin extends \litepubl\admin\Panel
{

    public function getcontent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->formtitle = $lang->name;
        $args->deletep = $plugin->deletep;
        return $this->admin->form('[checkbox=deletep]', $args);
    }

    public function processform()
    {
        $plugin = Plugin::i();
        $plugin->deletep = isset($_POST['deletep']);
        $plugin->save();
    }
}