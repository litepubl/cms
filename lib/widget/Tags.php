<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\widget;

use litepubl\tag\Tags as Owner;
use litepubl\view\Lang;

class Tags extends CommonTags
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.tags';
        $this->template = 'tags';
        $this->sortname = 'title';
        $this->showcount = false;
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'tags');
    }

    public function getOwner()
    {
        return Owner::i();
    }
}
