<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl\core\Arr;

class Order extends Widget
{

    protected function create()
    {
        parent::create();
        unset($this->id);
        $this->data['id'] = 0;
        $this->data['ajax'] = 'disabled';
        $this->data['order'] = 0;
        $this->data['sidebar'] = 0;
    }

    public function onsidebar(array & $items, int $sidebar)
    {
        if ($sidebar != $this->sidebar) {
            return;
        }

        $order = $this->order;
        if (($order < 0) || ($order >= count($items))) {
            $order = count($items);
        }

        Arr::insert(
            $items,
            [
            'id' => $this->id,
            'ajax' => $this->ajax
            ],
            $order
        );
    }
}
