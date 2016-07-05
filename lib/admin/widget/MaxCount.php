<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\admin\widget;

class MaxCount extends Widget
{

    protected function getForm()
    {
        $this->args->maxcount = $this->widget->maxcount;
        return parent::getForm() . '[text=maxcount]';
    }

    protected function doProcessForm()
    {
        $this->widget->maxcount = (int)$_POST['maxcount'];
    }
}
