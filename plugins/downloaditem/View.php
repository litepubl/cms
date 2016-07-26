<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\downloaditem;

use litepubl\plugins\polls\Polls;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class View extends \litepubl\post\View
{

    protected function getAuthorName(): string
    {
        return $this->post->authorname;
    }

    protected function getContentpage(int $page): string
    {
        $result= $this->getDownloadContent();
        $result .= parent::getcontentpage($page);
        $result.= Polls::i()->getObjectPoll($this->id, 'post');
        return $result;
    }

    public function getDownloadContent(): string
    {
        Theme::$vars['lang'] = Lang::i('downloaditem');
        Theme::$vars['post'] = $this;
        $theme = $this->theme;
        return $theme->parse($theme->templates['downloaditem']);
    }

    public function getDownloadCount()
    {
        return sprintf(Lang::get('downloaditem', 'downloaded'), $this->downloads);
    }
}
