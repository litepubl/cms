<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\view\Js;

function emailauthInstall($self)
{
    $js = Js::i();
    $js->lock();
    $js->add('default', '/plugins/ulogin/resource/email.auth.min.js');
    $js->unlock();

    $json = tjsonserver::i();
    $json->lock();
    $json->addevent('email_login', get_class($self) , 'email_login');
    $json->addevent('email_reg', get_class($self) , 'email_reg');
    $json->addevent('email_lostpass', get_class($self) , 'email_lostpass');
    $json->unlock();
}

function emailauthUninstall($self)
{
    $js = Js::i();
    $js->lock();
    $js->deletefile('default', '/plugins/ulogin/resource/email.auth.min.js');
    $js->unlock();

    tjsonserver::i()->unbind($self);
}

