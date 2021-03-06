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

use litepubl\widget\Custom as CustomWidget;

class AddCustom extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $widget = CustomWidget::i();
        return $widget->admin->getcontent();
    }

    public function processForm()
    {
        $widget = CustomWidget::i();
        return $widget->admin->processForm();
    }
}
