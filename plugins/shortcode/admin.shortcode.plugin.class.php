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

class tadminshortcodeplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $plugin = tshortcode::i();
        $about = Plugins::getabout(Plugins::getname(__file__));
        $args = new Args();

        $s = '';
        foreach ($plugin->items as $name => $value) {
            $s.= "$name = $value\n";
        }

        $args->codes = $s;
        $args->formtitle = $about['formtitle'];
        $args->data['$lang.codes'] = $about['codes'];

        $html = tadminhtml::i();
        return $html->adminform('[editor=codes]', $args);
    }

    public function processForm() {
        $plugin = tshortcode::i();
        //$plugin->setcodes($_POST['codes']);
        $plugin->items = parse_ini_string($_POST['codes'], false);
        $plugin->save();
    }

} //class