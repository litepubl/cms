<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\tag;
use litepubl\widget\Tags as TagsWidget;

class Tags extends Common
{

    public static function i() {
        return static::iGet(__class__);
    }

    protected function create() {
        parent::create();
        $this->table = 'tags';
        $this->basename = 'tags';
        $this->PermalinkIndex = 'tag';
        $this->postpropname = 'tags';
        $this->contents->table = 'tagscontent';
        $this->itemsposts->table = $this->table . 'items';
    }

    public function save() {
        parent::save();
        if (!$this->locked) {
            TagsWidget::i()->expire();
        }
    }

}