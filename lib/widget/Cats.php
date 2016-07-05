<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\widget;

use litepubl\tag\Cats as Owner;
use litepubl\view\Lang;

class Cats extends CommonTags
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.categories';
        $this->template = 'categories';
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'categories');
    }

    public function getOwner()
    {
        return Owner::i();
    }
}
