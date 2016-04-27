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

class tadminopenid {
    public static function i() {
        return static::iGet(__class__);
    }

    public function getContent() {
        $openid = topenid::i();
        $args = new Args();
        $args->confirm = $openid->confirm;
        $args->usebigmath = $openid->usebigmath;
        $args->trusted = implode("\n", $openid->trusted);

        $tml = '[checkbox:confirm]
    [checkbox:usebigmath]
    [editor:trusted]';
        $about = Plugins::getabout(Plugins::getname(__file__));
        $args->formtitle = $about['formtitle'];
        $args->data['$lang.confirm'] = $about['confirm'];
        $args->data['$lang.usebigmath'] = $about['usebigmath'];
        $args->data['$lang.trusted'] = $about['trusted'];

        $html = tadminhtml::i();
        return $html->adminform($tml, $args);
    }

    public function processForm() {
        extract($_POST, EXTR_SKIP);
        $openid = topenid::i();
        $openid->confirm = isset($confirm);
        $openid->usebigmath = isset($usebigmath);
        $openid->trusted = explode("\n", trim($trusted));
        $openid->save();
    }

}