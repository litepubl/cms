<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\widget;
use litepubl\widget\Widgets as WidgetItems;
use litepubl\widget\Sidebars;
use litepubl\admin\GetSchema;
use litepubl\admin\Link;
use litepubl\admin\Form;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Parser;
use litepubl\core\Str;
use litepubl\core\Arr;

class Widgets extends \litepubl\admin\Menu
{

    public static function getSidebarNames(tview $schema) {
        $count = $schema->theme->sidebarscount;
        $result = range(1, $count);
        $parser = Parser::i();
        $about = $parser->getabout($schema->theme->name);
        foreach ($result as $key => $value) {
            if (isset($about["sidebar$key"])) $result[$key] = $about["sidebar$key"];
        }

        return $result;
    }

    public function getCombobox($name, array $items, $selected) {
        return sprintf('<select name="%1$s" id="%1$s">%2$s</select>',
 $name, $this->theme->comboItems($items, $selected));
    }

   public function getTableForm() {
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
        $form->items.= $form->hidden('idschema', $idschema);

        if ($idschema != 1) {
            $form->body .= $theme->getinput('checkbox', 'customsidebar', 'checked="checked"', $lang->customsidebar);
        }
        //all widgets
        $checkboxes = '';
        foreach ($widgets->items as $id => $item) {
            if (!Sidebars::getpos($schema->sidebars, $id)) {
                $checkboxes.= $theme->getinput('checkbox', "addwidget-$id", "value=\"$id\"", $item['title']);
            }
        }

        $args->checkboxes = $checkboxes;
        $args->idschema = $idschema;
        $form->before = $admintheme->parseArg($admintheme->templates['addwidgets'], $args);
        $count = count($schema->sidebars);
        $sidebarnames = static ::getsidebarnames($schema);

        //items for table builder
        $items = array();
        $tml_btn = $admintheme->templates['radiogroup.button'];
        $tml_active = $admintheme->templates['radiogroup.active'];

        foreach ($schema->sidebars as $i => $sidebar) {
            $orders = range(1, count($sidebar));
            foreach ($sidebar as $j => $sb_item) {
                $id = $sb_item['id'];
                $w_item = $widgets->getitem($id);

                $items[] = array(
                    'id' => $id,
                    'title' => $w_item['title'],
                    'sidebarcombo' => $this->getCombobox("sidebar-$id", $sidebarnames, $i) ,
                    'ordercombo' => $this->getCombobox("order-$id", $orders, $j) ,
                    'ajaxbuttons' => str_replace('$button',

                    strtr($sb_item['ajax'] == false ? $tml_active : $tml_btn, array(
                        '$name' => "ajax-$id",
                        '$value' => 'noajax',
                        '$title' => $lang->noajax
                    )) .

                    strtr(($sb_item['ajax'] && $sb_item['ajax'] != 'inline') ? $tml_active : $tml_btn, array(
                        '$name' => "ajax-$id",
                        '$value' => 'ajax',
                        '$title' => $lang->ajax
                    )) .

                    (($w_item['cache'] == 'cache') || ($w_item['cache'] == 'nocache') ? strtr($sb_item['ajax'] == 'inline' ? $tml_active : $tml_btn, array(
                        '$name' => "ajax-$id",
                        '$value' => 'inline',
                        '$title' => $lang->inline
                    )) : '') , $admintheme->templates['radiogroup'])
                );
            }
        }

        $tb = $this->newTable();
        $tb->args->adminurl = Link::url('/admin/views/widgets/', 'idwidget');
        $tb->setStruct(array(
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
        ));

        $form->items.= $tb->build($items);
        return $form->get();
    }

    public function getContent() {
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
            $result.= $this->admintheme->form('
      [checkbox=customsidebar]
      [checkbox=disableajax]
      [hidden=idschema]
      [hidden=action]', $args);
        }

        return $result;
    }

    public function processForm() {
         $this->getApp()->cache->clear();

        $idwidget = (int)$this->getParam('idwidget', 0);
        $widgets = WidgetItems::i();

        if ($widgets->itemExists($idwidget)) {
            if (isset($_GET['action']) && ($_GET['action'] == 'delete')) {
                $idschema = (int)$this->getParam('idschema', 1);
                $sidebars = Sidebars::i($idschema);
                $sidebars->remove($idwidget);
                $result = $this->admintheme->success($this->lang->deleted);
            } else {
                $widget = $widgets->getwidget($idwidget);
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
                    foreach ($sidebars->items as $i => $items) {
                        foreach ($items as $j => $item) {
                            $id = $item['id'];
                            if (!isset($_POST["sidebar-$id"])) {
 continue;
}



                            $i2 = (int)$_POST["sidebar-$id"];
                            if ($i2 >= count($sidebars->items)) {
                                $i2 = count($sidebars->items) - 1;
                            }

                            $j2 = (int)$_POST["order-$id"];
                            if ($j2 > count($sidebars->items[$i2])) {
                                $j2 = count($sidebars[$i2]);
                            }

                            if ($i == $i2) {
                                Arr::move($sidebars->items[$i2], $j, $j2);
                            } else {
                                Arr::delete($sidebars->items[$i], $j);
                                Arr::insert($sidebars->items[$i2], $item, $j2);
                            }

                            $sidebars->items[$i2][$j2]['ajax'] = $_POST["ajax-$id"] == 'inline' ? 'inline' : ($_POST["ajax-$id"] == 'ajax');
                        }
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