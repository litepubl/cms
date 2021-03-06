<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\widget;

class ShowCount extends Widget
{

    protected function getForm(): string
    {
        $this->args->showcount = $this->widget->showcount;
        return parent::getForm() . '[checkbox=showcount]';
    }

    protected function doProcessForm()
    {
        $this->widget->showcount = isset($_POST['showcount']);
    }
}
