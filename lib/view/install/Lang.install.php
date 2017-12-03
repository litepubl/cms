<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\view;

function LangInstall($self)
{
    lang::$self = $self;
    //check double install
    if (count($self->ini) > 0) {
        return;
    }

    preloadlanguage($self, $self->getApp()->options->language);
    $self->getApp()->options->timezone = lang::get('installation', 'timezone');
}

function LangPreinstall($language)
{
    $lang = new Lang();
    Lang::$self = $lang;
    Lang::$self->getApp()->classes->instances[get_class($lang) ] = $lang;
    preloadlanguage($lang, $language);
}

function preloadlanguage($lang, $language)
{
    $dir = $lang->getApp()->paths->languages . $language . DIRECTORY_SEPARATOR;
    foreach ([
        'default',
        'admin',
        'install'
    ] as $name) {
        $ini = parse_ini_file($dir . $name . '.ini', true);
        $lang->ini = $ini + $lang->ini;
        $lang->loaded[] = $name;
    }
    date_default_timezone_set(lang::get('installation', 'timezone'));
}
