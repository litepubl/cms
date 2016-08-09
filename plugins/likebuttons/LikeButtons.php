<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\likebuttons;

use litepubl\view\Js;

class LikeButtons extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['facebook_appid'] = '290433841025058';
    }

    public function setFacebook_appid($appid)
    {
        if (($appid = trim($appid)) && ($appid != $this->facebook_appid)) {
            $this->data['facebook_appid'] = $appid;
            $this->save();

            Js::i()->addtext('default', 'facebook_appid', ";ltoptions.facebook_appid='$appid';");
        }
    }
}
