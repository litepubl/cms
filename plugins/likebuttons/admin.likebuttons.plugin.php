<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class adminlikebuttons {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $about = tplugins::getabout(tplugins::getname(__file__));
        $args = new Args();
        $args->formtitle = $about['name'];
        $args->facebookapp = likebuttons::i()->facebook_appid;
        $args->data['$lang.facebookapp'] = $about['facebookapp'];

        $html = tadminhtml::i();
        return $html->adminform('[text=facebookapp]', $args);
    }

    public function processForm() {
        likebuttons::i()->facebook_appid = $_POST['facebookapp'];
    }

} //class