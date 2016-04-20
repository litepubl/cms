<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function catbreadInstall($self) {
    Langmerger::i()->addplugin(basename(dirname(__file__)));
    $self->cats->onbeforecontent = $self->beforecat;

    $parser = tthemeparser::i();
    $parser->lock();
    $parser->parsed = $self->themeparsed;
    $parser->addtags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
    $parser->unlock();
    basetheme::clearcache();
}

function catbreadUninstall($self) {
    Langmerger::i()->deleteplugin(basename(dirname(__file__)));
    $self->cats->unbind($self);
    $parser = tthemeparser::i();
    $parser->lock();
    $parser->unbind($self);
    $parser->removetags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
    $parser->unlock();
    basetheme::clearcache();
}

function catbreadThemeparsed(catbread $self, basetheme $theme) {
    $tag1 = '$catbread.post';
    $tag2 = '$catbread.sim';

    foreach (array(
        'content.post',
        'shop.product'
    ) as $k) {
        if (isset($theme->templates[$k]) && !strpos($theme->templates[$k], '$catbread')) {
            $v = $theme->templates[$k];
            $replace = '$post.catlinks';

            switch ($self->similarpos) {
                case 'top':
                    $v = $tag2 . $v;
                    break;


                case 'before':
                    $replace = $tag2 . $replace;
                    break;


                default:
                    ////ignore
                    
            }

            switch ($self->breadpos) {
                case 'top':
                    $v = $tag1 . $v;
                    break;


                case 'before':
                    $replace = $tag1 . $replace;
                    break;


                case 'after':
                    $replace.= $tag1;
                    break;


                case 'replace':
                    $replace = $tag1;
                    break;


                default:
                    ////ignore
                    
            }

            if ($self->similarpos == 'after') {
                $replace.= $tag2;
            }

            $theme->templates[$k] = str_replace('$post.catlinks', $replace, $v);
    }
}

if (tthemeparser::i()->replacelang) {
    $lang = Lang::i('catbread');
    foreach (array(
        'catbread.items.childs',
        'catbread.similar',
    ) as $name) {
        $theme->templates[$name] = $theme->replacelang($theme->templates[$name], $lang);
    }
}
}