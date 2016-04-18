<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;
use litepubl\widget\Widgets as WidgetItems;
use litepubl\view\Theme;
use litepubl\admin\GetSchema;

class Custom extends Widget
{
use \litepubl\admin\Params;
use \litepubl\admin\Factory;

public function getAdminTheme() {
return $this->admin;
}

    public function getTemplates() {
        $result = array();
        $lang = $this->lang;
$lang->section = 'widgets';
        $result['widget'] = $lang->defaulttemplate;
        foreach (Theme::getwidgetnames() as $name) {
            $result[$name] = $lang->$name;
        }

        return $result;
    }

    public function getcontent() {
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
            $form .= GetSchema::combo(1);
            $args->mode = 'add';
            $item = array(
                'title' => '',
                'content' => '',
                'template' => 'widget'
            );
        }

        $args->idwidget = $id;
        $args->text = $item['content'];
        $args->template = $this->theme->comboItems($this->getTemplates() , $item['template']);

$form .= '[editor=text]
    [combo=template]
    [hidden=mode]
    [hidden=idwidget]';

        $result = $this->admin->form($form, $args);
        $lang = $this->lang;
        $tb = $this->newTable();
        $tb->setstruct(array(
            $tb->checkbox('widgetcheck') ,
            array(
                $lang->widgettitle,
                "<a href=\"$this->adminurl\$id\" title=\"\$title\">\$title</a>"
            ) ,
        ));

        $form = $this->newForm($args);
        $form->title = $lang->widgets;
        $result.= $form->getdelete($tb->build($widget->items));
        return $result;
    }

    public function processform() {
        $widget = $this->widget;
        if (isset($_POST['mode'])) {
            extract($_POST, EXTR_SKIP);
            switch ($mode) {
                case 'add':
                    $_GET['idwidget'] = $widget->add($idview, $title, $text, $template);
                    break;


                case 'edit':
                    $id = isset($_GET['idwidget']) ? (int)$_GET['idwidget'] : 0;
                    if ($id == 0) $id = isset($_POST['idwidget']) ? (int)$_POST['idwidget'] : 0;
                    $widget->edit($id, $title, $text, $template);
                    break;
                }
        } elseif (isset($_POST['delete'])) {
            $this->deleteWidgets($widget);
        }
    }

    public function deleteWidgets(twidget $widget) {
        $widgets = WidgetItems::i();
        $widgets->lock();
        $widget->lock();
        foreach ($_POST as $key => $value) {
            if (strbegin($key, 'widgetcheck-')) {
$widget->delete((int)$value);
}
        }
        $widget->unlock();
        $widgets->unlock();
    }

}