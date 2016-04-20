<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\likebuttons;

class Admin extends \litepubl\admin\Admin
{

    public function getContent() {
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->formtitle = $lang->name;
        $args->facebookapp = LikeButtons::i()->facebook_appid;
        return $this->admin->form('[text=facebookapp]', $args);
    }

    public function processForm() {
        LikeButtons::i()->facebook_appid = $_POST['facebookapp'];
    }

}