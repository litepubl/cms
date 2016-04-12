<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;

function LangInstall($self) {
    lang::$self = $self;
    //check double install
    if (count($self->ini) > 0) return;
    preloadlanguage($self, litepubl::$options->language);
    litepubl::$options->timezone = lang::get('installation', 'timezone');
}

function LangPreinstall($language) {
    $lang = new lang();
    lang::$self = $lang;
    litepubl::$classes->instances[get_class($lang)] = $lang;
    preloadlanguage($lang, $language);
}

function preloadlanguage($lang, $language) {
    $dir = litepubl::$paths->languages . $language . DIRECTORY_SEPARATOR;
    foreach (array(
        'default',
        'admin',
        'install'
    ) as $name) {
        $ini = parse_ini_file($dir . $name . '.ini', true);
        $lang->ini = $ini + $lang->ini;
        $lang->loaded[] = $name;
    }
    date_default_timezone_set(lang::get('installation', 'timezone'));
}