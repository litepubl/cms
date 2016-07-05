<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\admin\widget;

use litepubl\view\Lang;

class Tags extends Widget
{

    protected function getForm()
    {
        $args = $this->args;
        $widget = $this->widget;

        $args->showcount = $widget->showcount;
        $args->showsubitems = $widget->showsubitems;
        $args->maxcount = $widget->maxcount;
        $args->sort = $this->theme->comboItems(Lang::i()->ini['sortnametags'], $widget->sortname);

        return parent::getForm() . '[combo=sort]
 [checkbox=showsubitems]
 [checkbox=showcount]
 [text=maxcount]';
    }

    protected function doProcessForm()
    {
        extract($_POST, EXTR_SKIP);
        $this->widget->maxcount = (int)$maxcount;
        $this->widget->showcount = isset($showcount);
        $this->widget->showsubitems = isset($showsubitems);
        $this->widget->sortname = $sort;
    }
}
