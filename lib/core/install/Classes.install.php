<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

function parse_classes_ini($inifile) {
    $install_dir =  $self->getApp()->paths->lib . 'install/ini/';
    if (!$inifile) {
        $inifile = $install_dir . 'classes.ini';
    } elseif (file_exists($install_dir . $inifile)) {
        $inifile = $install_dir . $inifile;
    } elseif (file_exists( $self->getApp()->paths->home . $inifile)) {
        $inifile =  $self->getApp()->paths->home . $inifile;
    } elseif (!file_exists($inifile)) {
        $inifile = $install_dir . 'classes.ini';
    }

    $ini = parse_ini_file($inifile, true);
    $classes =  $self->getApp()->classes;
    foreach ($ini['items'] as $class => $filename) {
        $classes->items[$class] = "lib/$filename";
    }

    $kernel = parse_ini_file( $self->getApp()->paths->lib . 'install/ini/kernel.ini', false);
    foreach ($kernel as $class => $filename) {
        $classes->kernel[$class] = "lib/$filename";
    }

    $classes->classes = $ini['classes'];
    $classes->factories = $ini['factories'];
    $classes->Save();
}

function installClasses() {
     $self->getApp()->router = \litepubl\core\Router::i();
     $self->getApp()->router->lock();
    $posts = tposts::i();
    $posts->lock();
    $js = tjsmerger::i();
    $js->lock();

    $css = tcssmerger::i();
    $css->lock();

    $xmlrpc = TXMLRPC::i();
    $xmlrpc->lock();
    ttheme::$defaultargs = array();
    $theme = Theme::getTheme('default');
    foreach ( $self->getApp()->classes->items as $class => $item) {
        if (preg_match('/^(titem|titem_storage|titemspostsowner|tcomment|IXR_Client|IXR_Server|tautoform|tchildpost|tchildposts|cachestorage_memcache|thtmltag|ECancelEvent)$/', $class)) {
 continue;
}



        //ignore interfaces and traits
        if (class_exists('litepubl\\' . $class)) {
            //echo "$class<br>";
            $obj = getinstance('litepubl\\' . $class);
            if (method_exists($obj, 'install')) {
                $obj->install();
            }
        }
    }

    //default installed plugins
    $plugins = tplugins::i();
    $plugins->lock();
    $plugins->add('likebuttons');
    $plugins->add('oldestposts');
    $plugins->add('photoswipe');
    $plugins->add('photoswipe-thumbnail');
    $plugins->add('bootstrap-theme');
    $plugins->unlock();

    $xmlrpc->unlock();
    $css->unlock();
    $js->unlock();
    $posts->unlock();
     $self->getApp()->router->unlock();
}