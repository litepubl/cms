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


namespace litepubl\plugins\bootstrap;

use litepubl\core\Plugins;
use litepubl\view\Base;
use litepubl\view\Css;
use litepubl\view\Js;

function BbootstrapInstall($self)
{
    $js = Js::i();
    $js->lock();
    $js->externalfunc(
        get_class($js), '_switch', array(
        $js->externalfunc(get_class($js), '_bootstrap_files', false) ,
        $js->externalfunc(get_class($js), '_pretty_files', false)
        )
    );

    $js->externalfunc(get_class($js), '_bootstrap_admin', true);
    $js->externalfunc(get_class($js), '_ui_admin', false);

    $js_switch = $js->getExternalFuncName(get_class($js), '_switch');
    $css = Css::i();
    $css->lock();
    $js_switch($css, array() , $css->externalfunc(get_class($css), '_pretty_files', false));
    $js_switch($css, array() , $css->externalfunc(get_class($css), '_deprecated_files', false));
    $js_switch($css, $css->externalfunc(get_class($css), '_bootstrap_files', false) , array());

    //default installed plugins
    $plugins = Plugins::i();
    $plugins->lock();
    $plugins->add('likebuttons');
    $plugins->add('photoswipe');
    $plugins->unlock();

    $css->unlock();
    $js->unlock();
    Base::clearcache();
}

function BootstrapUninstall($self)
{
    $js = Js::i();
    $js->lock();
    $js->externalfunc(
        get_class($js), '_switch', array(
        $js->externalfunc(get_class($js), '_pretty_files', false) ,
        $js->externalfunc(get_class($js), '_bootstrap_files', false) ,
        )
    );

    $js->externalfunc(get_class($js), '_bootstrap_admin', false);

    $js->externalfunc(get_class($js), '_ui_admin', true);
    $js->unlock();

    $js_switch = $js->getExternalFuncName(get_class($js), '_switch');
    $css = Css::i();
    $css->lock();
    $js_switch($css, $css->externalfunc(get_class($css), '_pretty_files', false) , array());
    $js_switch($css, $css->externalfunc(get_class($css), '_deprecated_files', false) , array());
    $js_switch($css, array() , $css->externalfunc(get_class($css), '_bootstrap_files', false));
    $css->unlock();

    Base::clearcache();
}
