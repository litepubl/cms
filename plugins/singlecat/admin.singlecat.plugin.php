<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Args;
use litepubl\core\Plugins;

class tadminsinglecat {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $plugin = tsinglecat::i();
        $html = tadminhtml::i();
        $lang = Plugins::getlangabout(__file__);
        $args = new Args();
        $args->maxcount = $plugin->maxcount;
        $args->invertorder = $plugin->invertorder;
        $args->tml = $plugin->tml;
        $args->tmlitems = $plugin->tmlitems;
        $args->formtitle = $lang->formtitle;
        return $html->adminform(' [checkbox=invertorder]
    [text=maxcount]
    [editor=tml]
    [editor=tmlitems]', $args);
    }

    public function processForm() {
        $plugin = tsinglecat::i();
        $plugin->invertorder = isset($_POST['invertorder']);
        $plugin->maxcount = (int)$_POST['maxcount'];
        $plugin->tml = $_POST['tml'];
        $plugin->tmlitems = $_POST['tmlitems'];
        $plugin->save();
    }

}