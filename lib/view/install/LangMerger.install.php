<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\view;

function LangMergerInstall($self)
{
    $dir = $self->getApp()->paths->data . 'languages';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777);
    }
    @chmod($dir, 0777);

    $lang = $self->getApp()->options->language;
    $self->lock();
    $self->add('default', "lib/languages/$lang/default.ini");

    $self->add('admin', "lib/languages/$lang/admin.ini");

    $self->add('mail', "lib/languages/$lang/mail.ini");

    if ($self->getApp()->options->language != 'en') {
        $self->add('translit', "lib/languages/$lang/translit.ini");
    } else {
        $self->items['translit'] = array(
            'files' => array() ,
            'texts' => array()
        );
    }

    $self->add('install', "lib/languages/$lang/install.ini");
    $self->unlock();

    //after install
    $self->getApp()->options->timezone = lang::get('installation', 'timezone');
    date_default_timezone_set(lang::get('installation', 'timezone'));
}
