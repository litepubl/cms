<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\oldestposts;

class Admin extends \litepubl\admin\widget\Order
{

    public function __construct()
    {
        parent::__construct();
        $this->widget = Oldestposts::i();
    }

    protected function getForm(): string
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
