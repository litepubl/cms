<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\bootstrap;
use litepubl\view\Js;
use litepubl\view\Css;
use litepubl\view\Base;
use litepubl\core\Plugins;

function BbootstrapInstall($self) {
    $js = Js::i();
    $js->lock();
    $js->externalfunc(get_class($js) , '_switch', array(
        $js->externalfunc(get_class($js) , '_bootstrap_files', false) ,
        $js->externalfunc(get_class($js) , '_pretty_files', false)
    ));

    tjsmerger_bootstrap_admin($js, true);
    tjsmerger_ui_admin($js, false);

    $css = Css::i();
    $css->lock();
    tjsmerger_switch($css, array() , $css->externalfunc(get_class($css) , '_pretty_files', false));

    tjsmerger_switch($css, array() , $css->externalfunc(get_class($css) , '_deprecated_files', false));

    tjsmerger_switch($css, $css->externalfunc(get_class($css) , '_bootstrap_files', false) , array());

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

function BootstrapUninstall($self) {
    $js = Js::i();
    $js->lock();
    $js->externalfunc(get_class($js) , '_switch', array(
        $js->externalfunc(get_class($js) , '_pretty_files', false) ,
        $js->externalfunc(get_class($js) , '_bootstrap_files', false) ,
    ));

    tjsmerger_bootstrap_admin($js, false);
    tjsmerger_ui_admin($js, true);
    $js->unlock();

    $css = Css::i();
    $css->lock();
    tjsmerger_switch($css, $css->externalfunc(get_class($css) , '_pretty_files', false) , array());
    tjsmerger_switch($css, $css->externalfunc(get_class($css) , '_deprecated_files', false) , array());
    tjsmerger_switch($css, array() , $css->externalfunc(get_class($css) , '_bootstrap_files', false));
    $css->unlock();

    Base::clearcache();
}