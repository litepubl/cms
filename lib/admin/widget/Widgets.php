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

use litepubl\admin\Form;
use litepubl\admin\GetSchema;
use litepubl\admin\Link;
use litepubl\core\Arr;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Parser;
use litepubl\view\Schema;
use litepubl\widget\Sidebars;
use litepubl\widget\Widgets as WidgetItems;

class Widgets extends \litepubl\admin\Menu
{

    public static function getSidebarNames(Schema $schema)
    {
        $count = $schema->theme->sidebarscount;
        $result = range(1, $count);
        $parser = Parser::i();
        $about = $parser->getabout($schema->theme->name);
        foreach ($result as $key => $value) {
            if (isset($about["sidebar$key"])) {
                $result[$key] = $about["sidebar$key"];
            }
        }

        return $result;
    }

    public function getCombobox($name, array $items, $selected)
    {
        return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name, $this->theme->comboItems($items, $selected));
    }

    public function getTableForm()
    {
        $idschema = (int)$this->getparam('idschema', 1);
        $schema = Schema::i($idschema);

        $widgets = WidgetItems::i();
        $theme = $this->theme;
        $admintheme = $this->admintheme;

        $lang = Lang::i('widgets');
        $lang->addsearch('views');
        $args = new Args();
        $form = new Form($args);
        $form->title = $lang->formhead;
        $form->body = $form->hidden('action', 'edit');
        $form->body.= $form->hidden('idschema', $idschema);

        if ($idschema != 1) {
            $form->body.= $theme->getInput('checkbox', 'customsidebar', 'checked="checked"', $lang->customsidebar);
        }
        //all widgets
        $checkboxes = '';
        foreach ($widgets->items as $id => $item) {
            if (!Sidebars::getPos($schema->sidebars, $id)) {
                $checkboxes.= $theme->getInput('checkbox', "addwidget-$id", "value=\"$id\"", $item['title']);
            }
        }

        $args->checkboxes = $checkboxes;
        $args->idschema = $idschema;
        $form->before = $admintheme->parseArg($admintheme->templates['addwidgets'], $args);
        $count = count($schema->sidebars);
        $sidebarnames = static ::getSidebarNames($schema);

        //items for table builder
        $items = array();
        $tmlButton = $admintheme->templates['radiogroup.button'];
        $tmlActiveButton = $admintheme->templates['radiogroup.active'];

        foreach ($schema->sidebars as $i => $sidebar) {
            $orders = range(1, count($sidebar));
            foreach ($sidebar as $j => $sidebarItem) {
                $id = $sidebarItem['id'];
                $ajax = $sidebarItem['ajax'];
                if ($ajax === true) {
                                $ajax = 'ajax';
                }

                if (!$ajax) {
                                $ajax = 'disabled';
                }

                $widgetItem = $widgets->getItem($id);

                $items[] = array(
                    'id' => $id,
                    'title' => $widgetItem['title'],
                    'sidebarcombo' => $this->getCombobox("sidebar-$id", $sidebarnames, $i) ,
                    'ordercombo' => $this->getCombobox("order-$id", $orders, $j) ,
                    'ajaxbuttons' => str_replace(
                        '$button',
                        strtr(
                            $ajax == 'disabled' ? $tmlActiveButton : $tmlButton, array(
                            '$name' => "ajax-$id",
                            '$value' => 'disabled',
                            '$title' => $lang->noajax
                            )
                        )

                        . strtr(
                            $ajax == 'ajax' ? $tmlActiveButton : $tmlButton, array(
                            '$name' => "ajax-$id",
                            '$value' => 'ajax',
                            '$title' => $lang->ajax
                            )
                        )

                        . (($widgetItem['cache'] == 'cache') || ($widgetItem['cache'] == 'nocache') ? strtr(
                            $ajax == 'inline' ? $tmlActiveButton : $tmlButton, array(
                            '$name' => "ajax-$id",
                            '$value' => 'inline',
                            '$title' => $lang->inline
                            )
                        ) : ''),
                        $admintheme->templates['radiogroup']
                    )
                );
            }
        }

        $tb = $this->newTable();
        $tb->args->adminurl = Link::url('/admin/views/widgets/', 'idwidget');
        $tb->setStruct(
            array(
            array(
                $lang->widget,
                '<a href="$adminurl=$id">$title</a>'
            ) ,

            array(
                $lang->sidebar,
                '$sidebarcombo'
            ) ,

            array(
                $lang->order,
                '$ordercombo'
            ) ,

            array(
                $lang->delete,
                '<a href="$adminurl=$id&action=delete" class="btn btn-default confirm-delete-link" role="button"><span class="fa fa-remove text-danger"></span> $lang.delete</a>',
            ) ,

            array(
                $lang->collapse,
                '$ajaxbuttons'
            )
            )
        );

        $form->items.= $tb->build($items);
        return $form->get();
    }

    public function getContent(): string
    {
        if (!(isset($_GET['action']) && $_GET['action'] == 'delete')) {
            $idwidget = $this->getparam('idwidget', 0);
            $widgets = WidgetItems::i();
            if ($widgets->itemExists($idwidget)) {
                $widget = $widgets->getwidget($idwidget);
                return $widget->admin->getcontent();
            }
        }

        $idschema = (int)$this->getParam('idschema', 1);
        $schema = Schema::i($idschema);
        $result = GetSchema::form('/admin/views/widgets/');

        if (($idschema == 1) || $schema->customsidebar) {
            $result.= $this->getTableForm();
        } else {
            $lang = Lang::i('widgets');
            $args = new Args();
            $args->customsidebar = $schema->customsidebar;
            $args->disableajax = $schema->disableajax;
            $args->idschema = $idschema;
            $args->action = 'options';
            $args->formtitle = $lang->viewsidebar;
            $result.= $this->admintheme->form(
                '
      [checkbox=customsidebar]
      [checkbox=disableajax]
      [hidden=idschema]
      [hidden=action]', $args
            );
        }

        return $result;
    }

    public function processForm()
    {
        $idwidget = (int)$this->getParam('idwidget', 0);
        $widgets = WidgetItems::i();

        if ($widgets->itemExists($idwidget)) {
            if (isset($_GET['action']) && ($_GET['action'] == 'delete')) {
                $idschema = (int)$this->getParam('idschema', 1);
                $sidebars = Sidebars::i($idschema);
                $sidebars->remove($idwidget);
                $result = $this->admintheme->success($this->lang->deleted);
            } else {
                $widget = $widgets->getWidget($idwidget);
                $result = $widget->admin->processForm();
            }

            return $result;
        }

        $idschema = (int)$this->getParam('idschema', 1);
        $schema = Schema::i($idschema);

        switch ($_POST['action']) {
        case 'options':
            $schema->disableajax = isset($_POST['disableajax']);
            $schema->customsidebar = isset($_POST['customsidebar']);
            $schema->save();
            break;


        case 'edit':
            if (($schema->id > 1) && !isset($_POST['customsidebar'])) {
                $schema->customsidebar = false;
            } else {
                $sidebars = Sidebars::i($idschema);
                $newItems = array_fill(0, count($sidebars->items), []);

                foreach ($sidebars->items as $i => $items) {
                    foreach ($items as $j => $item) {
                        $id = $item['id'];
                        if (!isset($_POST["sidebar-$id"])) {
                            Arr::append($newItems[$i], $j, $item);
                            continue;
                        }

                        $item['ajax'] = $_POST["ajax-$id"] == 'inline' ? 'inline' : ($_POST["ajax-$id"] == 'ajax' ? 'ajax' : 'disabled');

                        $i2 = (int)$_POST["sidebar-$id"];
                        if ($i2 >= count($sidebars->items)) {
                            $i2 = count($sidebars->items) - 1;
                        }

                        $j2 = (int)$_POST["order-$id"];
                        if ($j2 > count($sidebars->items[$i2])) {
                            $j2 = count($sidebars->items[$i2]);
                        }

                        Arr::append($newItems[$i2], $j2, $item);
                    }
                }

                foreach ($newItems as $i => $items) {
                    ksort($items);
                    Arr::reIndex($items);
                    $sidebars->items[$i] = $items;
                }

                $sidebars->save();
            }
            break;


        case 'add':
            $idschema = (int)$this->getParam('id_view', 1);
            $_GET['idschema'] = $idschema;
            $schema = Schema::i($idschema);
            $widgets = WidgetItems::i();

            foreach ($_POST as $key => $value) {
                if (Str::begin($key, 'addwidget-')) {
                    $id = (int)$value;
                    if (!$widgets->itemExists($id) || $widgets->subclass($id)) {
                        continue;
                    }

                    $schema->sidebars[0][] = array(
                        'id' => $id,
                        'ajax' => false
                    );
                }
            }
            break;
        }

        $schema->save();
    }
}
