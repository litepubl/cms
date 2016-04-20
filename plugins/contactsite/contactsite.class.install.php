<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tcontactsiteInstall($self) {
    $theme = ttheme::i();
    $args = new Args();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->add($about);
    $self->title = $about['title'];
    $self->subject = $about['subject'];
    $self->success = $theme->parsearg('<p><strong>$success</strong></p>', $args);
    $self->errmesg = $theme->parsearg('<p><strong>$errmesg</strong></p>', $args);

    $form = $theme->parsearg(file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'form.tml') , $args);
    $self->data['content'] = $form;
    $self->data['rawcontent'] = $form;

    $self->order = 9;

    $menus = tmenus::i();
    $menus->add($self);
}

function tcontactsiteUninstall($self) {
    $menus = tmenus::i();
    $menus->lock();
    while ($id = $menus->class2id(get_class($self))) $menus->delete($id);
    $menus->unlock();
}