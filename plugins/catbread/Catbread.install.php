<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\catbread;

use litepubl\view\AutoVars;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\view\LangMerger;
use litepubl\view\Parser;

function CatbreadInstall($self)
{
    LangMerger::i()->addplugin(basename(dirname(__file__)));
    $self->cats->view->onBeforeContent = $self->beforeCat;

    $parser = Parser::i();
    $parser->lock();
    $parser->parsed = $self->themeParsed;
    $parser->addtags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
    $parser->unlock();

    AutoVars::i()->add('catbread', get_class($self));
    Base::clearcache();
}

function CatbreadUninstall($self)
{
    LangMerger::i()->deleteplugin(basename(dirname(__file__)));
    $self->cats->unbind($self);
    $parser = Parser::i();
    $parser->lock();
    $parser->unbind($self);
    $parser->removeTags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
    $parser->unlock();
    AutoVars::i()->delete('catbread');
    Base::clearcache();
}

function CatbreadThemeparsed(Catbread $self, Base $theme)
{
    $tag1 = '$catbread.post';
    $tag2 = '$catbread.sim';

    foreach ([
        'content.post',
        'shop.product'
    ] as $k) {
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

    if (Parser::i()->replacelang) {
        $lang = Lang::i('catbread');
        foreach ([
        'catbread.items.childs',
        'catbread.similar',
        ] as $name) {
            $theme->templates[$name] = $theme->replacelang($theme->templates[$name], $lang);
        }
    }
}
