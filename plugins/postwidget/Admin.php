<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\postwidget;

use litepubl\widget\Widgets;

class Admin extends \litepubl\admin\widget\Custom
{

    public function getContent()
    {
        $widget = Widget::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $id = (int)$this->getParam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $item = $widget->items[$id];
            $args->mode = 'edit';
            $args->idwidget = $id;
        } else {
            $item = array(
                'title' => '',
                'content' => '',
                'template' => 'widget',
                'cats' => array()
            );
            $args->mode = 'add';
            $args->idwidget = 0;
        }

        $cats = $this->admin->getCats($item['cats']);
        $args->add($item);
        $args->widgettitle = $item['title'];
        $args->template = $this->theme->comboItems(static ::gettemplates() , $item['template']);
        $args->formtitle = $item['title'] == '' ? $this->lang->widget : $item['title'];
        $result = $this->admin->form('
    [text=widgettitle]
    [editor=content]
    [combo=template]
    [hidden=idwidget]
    [hidden=mode]
' . $this->admin->h($lang->cats)
 . $cats, $args);

        $result.= $this->getList($widget);
        return $result;
    }

    public function processForm()
    {
        $widget = Widget::i();
        if (isset($_POST['mode'])) {
            extract($_POST, EXTR_SKIP);
            switch ($mode) {
                case 'add':
                    $_GET['idwidget'] = $widget->add($widgettitle, $content, $template, $this->admin->processCategories());
                    break;


                case 'edit':
                    $id = isset($_GET['idwidget']) ? (int)$_GET['idwidget'] : 0;
                    if ($id == 0) $id = isset($_POST['idwidget']) ? (int)$_POST['idwidget'] : 0;
                    $item = $widget->items[$id];
                    $item['title'] = $widgettitle;
                    $item['content'] = $content;
                    $item['template'] = $template;
                    $item['cats'] = $this->admin->processCategories();
                    $widget->items[$id] = $item;
                    $widget->save();

                    $widgets = Widgets::i();
                    $widgets->items[$id]['title'] = $widgettitle;
                    $widgets->save();
                    break;
                }
        } else {
            $this->deleteWidgets($widget);
        }
    }

}
