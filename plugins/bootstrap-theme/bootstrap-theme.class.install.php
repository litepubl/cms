<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function bootstrap_themeInstall($self) {
    $js = tjsmerger::i();
    $js->lock();

    $js->externalfunc(get_class($js) , '_switch', array(
        $js->externalfunc(get_class($js) , '_bootstrap_files', false) ,
        $js->externalfunc(get_class($js) , '_pretty_files', false)
    ));

    tjsmerger_bootstrap_admin($js, true);
    tjsmerger_ui_admin($js, false);

    $css = tcssmerger::i();
    $css->lock();
    tjsmerger_switch($css, array() , $css->externalfunc(get_class($css) , '_pretty_files', false));

    tjsmerger_switch($css, array() , $css->externalfunc(get_class($css) , '_deprecated_files', false));

    tjsmerger_switch($css, $css->externalfunc(get_class($css) , '_bootstrap_files', false) , array());

    //default installed plugins
    $plugins = tplugins::i();
    $plugins->lock();
    $plugins->add('likebuttons');
    $plugins->add('photoswipe');
    $plugins->unlock();

    $css->unlock();
    $js->unlock();

    ttheme::clearcache();
}

function bootstrap_themeUninstall($self) {
    $js = tjsmerger::i();
    $js->lock();
    $js->externalfunc(get_class($js) , '_switch', array(
        $js->externalfunc(get_class($js) , '_pretty_files', false) ,
        $js->externalfunc(get_class($js) , '_bootstrap_files', false) ,
    ));

    tjsmerger_bootstrap_admin($js, false);
    tjsmerger_ui_admin($js, true);
    $js->unlock();

    $css = tcssmerger::i();
    $css->lock();
    tjsmerger_switch($css, $css->externalfunc(get_class($css) , '_pretty_files', false) , array());
    tjsmerger_switch($css, $css->externalfunc(get_class($css) , '_deprecated_files', false) , array());
    tjsmerger_switch($css, array() , $css->externalfunc(get_class($css) , '_bootstrap_files', false));
    $css->unlock();

    ttheme::clearcache();
}