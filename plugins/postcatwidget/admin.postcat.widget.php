<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tadminpostcatwidget extends tadmincustomwidget {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $widget = tpostcatwidget::i();
        $about = tplugins::getabout(tplugins::getname(__file__));
        $args = new Args();
        $id = (int)$this->getparam('idwidget', 0);
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

        $cats = admintheme::i()->getcats($item['cats']);
        $html = $this->html;
        $html->section = 'widgets';
        $args->add($item);
        $args->widgettitle = $item['title'];
        $args->template = $this->theme->comboItems(static ::gettemplates() , $item['template']);
        $args->formtitle = $item['title'] == '' ? $this->lang->widget : $item['title'];
        $result = $html->adminform('
    [text=widgettitle]
    [editor=content]
    [combo=template]
    [hidden=idwidget]
    [hidden=mode]' . sprintf('<h4>%s</h4>', $about['cats']) . $cats, $args);
        $result.= $this->getlist($widget);
        return $result;
    }

    public function processForm() {
        $widget = tpostcatwidget::i();
        if (isset($_POST['mode'])) {
            extract($_POST, EXTR_SKIP);
            switch ($mode) {
                case 'add':
                    $_GET['idwidget'] = $widget->add($widgettitle, $content, $template, admintheme::i()->processcategories());
                    break;


                case 'edit':
                    $id = isset($_GET['idwidget']) ? (int)$_GET['idwidget'] : 0;
                    if ($id == 0) $id = isset($_POST['idwidget']) ? (int)$_POST['idwidget'] : 0;
                    $item = $widget->items[$id];
                    $item['title'] = $widgettitle;
                    $item['content'] = $content;
                    $item['template'] = $template;
                    $item['cats'] = admintheme::i()->processcategories();
                    $widget->items[$id] = $item;
                    $widget->save();

                    $widgets = twidgets::i();
                    $widgets->items[$id]['title'] = $widgettitle;
                    $widgets->save();
                    break;
                }
        } else {
            $this->deletewidgets($widget);
        }
    }

} //class