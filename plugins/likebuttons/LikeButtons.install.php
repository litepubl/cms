<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\likebuttons;

use litepubl\view\Js;

function LikeButtonsInstall($self)
{
    $name = basename(dirname(__file__));
    $js = Js::i();
    $js->lock();
    $js->add('default', "plugins/$name/resource/likebuttons.min.js");

    $js->addtext('default', 'facebook_appid', ";ltoptions.facebook_appid='$self->facebook_appid';");

    $js->unlock();
}

function LikeButtonsUninstall($self)
{
    $name = basename(dirname(__file__));
    $js = Js::i();
    $js->lock();
    $js->deletefile('default', "plugins/$name/resource/likebuttons.min.js");

    $js->deletetext('default', 'facebook_appid');
    $js->unlock();
}
