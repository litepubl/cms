<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\view\Theme;
use litepubl\core\Arr;

class Order extends Widget
 {

    protected function create() {
        parent::create();
        unset($this->id);
        $this->data['id'] = 0;
        $this->data['ajax'] = false;
        $this->data['order'] = 0;
        $this->data['sidebar'] = 0;
    }

    public function onsidebar(array & $items, $sidebar) {
        if ($sidebar != $this->sidebar) {
 return;
}


        $order = $this->order;
        if (($order < 0) || ($order >= count($items))) $order = count($items);
        Arr::insert($items, array(
            'id' => $this->id,
            'ajax' => $this->ajax
        ) , $order);
    }

}