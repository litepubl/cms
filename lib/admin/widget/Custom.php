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

use litepubl\admin\GetSchema;
use litepubl\core\Str;
use litepubl\view\Parser;
use litepubl\widget\Widget as WidgetItem;
use litepubl\widget\Widgets as WidgetItems;

class Custom extends Widget
{
    use \litepubl\admin\Params;
    use \litepubl\admin\Factory;

    public function getAdminTheme()
    {
        return $this->admin;
    }

    public function getTemplates()
    {
        $result = [];
        $lang = $this->lang;
        $lang->section = 'widgets';
        $result['widget'] = $lang->defaulttemplate;
        foreach (Parser::getWidgetNames() as $name) {
            $result[$name] = $lang->$name;
        }

        return $result;
    }

    public function getContent(): string
    {
        $widget = $this->widget;
        $args = $this->args;
        $id = (int)$this->getparam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $item = $widget->items[$id];
            $args->mode = 'edit';
            $form = $this->theme->getinput('text', 'title', $item['title'], $this->lang->widgettitle);
        } else {
            $id = 0;
            $form = $this->theme->getinput('text', 'title', '', $this->lang->widgettitle);
            $form.= GetSchema::combo(1);
            $args->mode = 'add';
            $item = [
                'title' => '',
                'content' => '',
                'template' => 'widget'
            ];
        }

        $args->idwidget = $id;
        $args->text = $item['content'];
        $args->template = $this->theme->comboItems($this->getTemplates(), $item['template']);

        $form.= '
[editor=text]
    [combo=template]
    [hidden=mode]
    [hidden=idwidget]';

        $result = $this->admin->form($form, $args);
        $result .= $this->getTableWidgets();
        return $result;
    }

    protected function getTableWidgets()
    {
        $lang = $this->lang;
        $tb = $this->newTable();
        $tb->setStruct(
            [
            $tb->checkbox('widgetcheck') ,
            [
                $lang->widgettitle,
                "<a href=\"$this->adminurl\$id\" title=\"\$title\">\$title</a>"
            ] ,
            ]
        );

        $form = $this->newForm($this->args);
        $form->title = $lang->widgets;
        return $form->getDelete($tb->build($this->widget->items));
    }

    public function processForm()
    {
        $widget = $this->widget;
        if (isset($_POST['mode'])) {
            extract($_POST, EXTR_SKIP);
            switch ($mode) {
                case 'add':
                    $_GET['idwidget'] = $widget->add($idschema, $title, $text, $template);
                    break;


                case 'edit':
                    $id = isset($_GET['idwidget']) ? (int)$_GET['idwidget'] : 0;
                    if ($id == 0) {
                        $id = isset($_POST['idwidget']) ? (int)$_POST['idwidget'] : 0;
                    }
                    $widget->edit($id, $title, $text, $template);
                    break;
            }
        } elseif (isset($_POST['delete'])) {
            $this->deleteWidgets($widget);
        }
    }

    public function deleteWidgets(WidgetItem $widget)
    {
        $widgets = WidgetItems::i();
        $widgets->lock();
        $widget->lock();
        foreach ($_POST as $key => $value) {
            if (Str::begin($key, 'widgetcheck-')) {
                $widget->delete((int)$value);
            }
        }
        $widget->unlock();
        $widgets->unlock();
    }
}
