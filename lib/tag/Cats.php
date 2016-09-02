<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\tag;

use litepubl\widget\Cache;
use litepubl\widget\Cats as CatsWidget;

/**
 * This is the categories class
 *
 * @property int $defaultid
 */

class Cats extends Common
{
    protected function create()
    {
        parent::create();
        $this->table = 'categories';
        $this->contents->table = 'catscontent';
        $this->itemsposts->table = $this->table . 'items';
        $this->basename = 'categories';
        $this->data['defaultid'] = 0;
    }

    public function setDefaultid($id)
    {
        if (($id != $this->defaultid) && $this->itemExists($id)) {
            $this->data['defaultid'] = $id;
            $this->save();
        }
    }

    public function save()
    {
        parent::save();
        if (!$this->locked) {
            Cache::i()->removeWidget(CatsWidget::i());
        }
    }
}
