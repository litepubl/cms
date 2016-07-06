<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\sameposts;

class Admin extends \litepubl\admin\widget\Order
{

    public function __construct()
    {
        parent::__construct();
        $this->widget = Widget::i();
        $this->widget->id = $this->widget->getWidgets()->find($this->widget);
    }

    protected function getForm()
    {
        $result= parent::getForm();
        $this->args->maxcount = $this->widget->maxcount;
        $result .= '[text=maxcount]';
        return $result;
    }

    protected function doProcessForm()
    {
        $this->widget->maxcount = (int)$_POST['maxcount'];
        $this->widget->postsChanged();
        return parent::doProcessForm();
    }
}
