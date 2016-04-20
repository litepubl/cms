<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tadminfriendswidget extends tadminwidget {

    public static function i() {
        return getinstance(__class__);
    }

    protected function dogetcontent(twidget $widget, targs $args) {
        $args->maxcount = $widget->maxcount;
        $args->redir = $widget->redir;
        return tadminhtml::i()->parsearg('[checkbox=redir] [text=maxcount]', $args);
    }

    protected function doProcessForm(twidget $widget) {
        $widget->maxcount = (int)$_POST['maxcount'];
        $widget->redir = isset($_POST['redir']);
    }

} //class