<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\options;

use litepubl\post\Rss as PostRss;
use litepubl\view\Args;
use litepubl\view\Lang;

class Rss extends \litepubl\admin\Menu
{
    public function getContent(): string
    {
        $rss = PostRss::i();
        $args = new Args();
        $args->feedburner = $rss->feedburner;
        $args->feedburnercomments = $rss->feedburnercomments;
        $args->template = $rss->template;

        $lang = Lang::admin('options');
        $args->formtitle = $lang->rssoptions;
        return $this->admintheme->form(
            '
[text=feedburner]
[text=feedburnercomments]
[editor=template]
',
            $args
        );
    }

    public function processForm()
    {
        $rss = PostRss::i();
        $rss->feedburner = trim($_POST['feedburner']);
        $rss->feedburnercomments = trim($_POST['feedburnercomments']);
        $rss->template = trim($_POST['template']);
        $rss->save();
    }
}
