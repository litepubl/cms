<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\widget;

class Meta extends Widget
{

    protected function getForm() {
        $result = parent::getForm();
$theme = $this->theme;
        foreach ($this->widget->items as $name => $item) {
            $result.= $theme->getinput('checkbox', $name, $item['enabled'] ? 'checked="checked"' : '', $item['title']);
        }

        return $result;
    }

    protected function doProcessForm() {
        foreach ($this->widget->items as $name => $item) {
            $widget->items[$name]['enabled'] = isset($_POST[$name]);
        }
    }

}