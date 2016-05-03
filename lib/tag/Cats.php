<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\tag;
use litepubl\widget\Cats as CatsWidget;

class Cats extends Common
{
    //public  $defaultid;

    protected function create() {
        parent::create();
        $this->table = 'categories';
        $this->contents->table = 'catscontent';
        $this->itemsposts->table = $this->table . 'items';
        $this->basename = 'categories';
        $this->data['defaultid'] = 0;
    }

    public function setDefaultid($id) {
        if (($id != $this->defaultid) && $this->itemExists($id)) {
            $this->data['defaultid'] = $id;
            $this->save();
        }
    }

    public function save() {
        parent::save();
        if (!$this->locked) {
            CatsWidget::i()->expire();
        }
    }

}