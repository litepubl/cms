<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\oldestposts;

class Admin extends \litepubl\admin\widget\Order
{

    protected function create()
    {
        parent::create();
        $this->widget = Oldestposts::i();
    }

    protected function getForm()
    {
        $this->args->maxcount = $this->widget->maxcount;
        return parent::getForm() . '[text=maxcount]';
    }

    protected function doProcessForm()
    {
        $this->widget->maxcount = (int)$_POST['maxcount'];
        return parent::doProcessForm();
    }

}

