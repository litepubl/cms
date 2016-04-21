<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\views;
use litepubl\admin\GetSchema;
use litepubl\core\Str;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Theme;

class Schemes extends \litepubl\admin\Menu
{

    public static function replacemenu($src, $dst) {
        $schemes = Schemes::i();
        foreach ($schemes->items as & $schemaitem) {
            if ($schemaitem['menuclass'] == $src) $schemaitem['menuclass'] = $dst;
        }
        $schemes->save();
    }

    private function get_custom(tview $schema) {
        $result = '';
        $theme = $this->theme;
        $customadmin = $schema->theme->templates['customadmin'];

        foreach ($schema->data['custom'] as $name => $value) {
            if (!isset($customadmin[$name])) {
 continue;
}


            switch ($customadmin[$name]['type']) {
                case 'text':
                case 'editor':
                    $value = Theme::quote($value);
                    break;


                case 'checkbox':
                    $value = $value ? 'checked="checked"' : '';
                    break;


                case 'combo':
                    $value = $theme->comboItems($customadmin[$name]['values'], array_search($value, $customadmin[$name]['values']));
                    break;


                case 'radio':
                    $value = $theme->getRadioItems("custom-$name", $customadmin[$name]['values'], array_search($value, $customadmin[$name]['values']));
                    break;
            }

            $result.= $theme->getinput($customadmin[$name]['type'], "custom-$name", $value, tadminhtml::specchars($customadmin[$name]['title']));
        }

        return $result;
    }

    private function set_custom($idschema) {
        $schema = Schema::i($idschema);
        if (count($schema->custom) == 0) {
 return;
}


        $customadmin = $schema->theme->templates['customadmin'];
        foreach ($schema->data['custom'] as $name => $value) {
            if (!isset($customadmin[$name])) {
 continue;
}


            switch ($customadmin[$name]['type']) {
                case 'checkbox':
                    $schema->data['custom'][$name] = isset($_POST["custom-$name"]);
                    break;


                case 'radio':
                case 'combo':
                    $schema->data['custom'][$name] = $customadmin[$name]['values'][(int)$_POST["custom-$name"]];
                    break;


                default:
                    $schema->data['custom'][$name] = $_POST["custom-$name"];
                    break;
            }
        }
    }

    public function getContent() {
        $result = '';
        $schemes = Schemes::i();
        $html = $this->html;
        $lang = Lang::i('views');
        $args = new Args();

        switch ($this->name) {
            case 'views':
                $lang->addsearch('views');

                $id = $this->getparam('idschema', 0);
                if (!$id || !$schemes->itemexists($id)) {
                    $adminurl = $this->adminurl . 'view';
                    $result = $html->h4($html->getlink($this->url . '/addview/', $lang->add));

                    $tb = new Table();
                    $tb->setstruct(array(
                        array(
                            $lang->name,
                            "<a href=\"$adminurl=\$id\"><span class=\"fa fa-cog\"></span> \$name</a>"
                        ) ,

                        array(
                            $lang->widgets,
                            "<a href=\"{$this->link}widgets/?idschema=\$id\"><span class=\"fa fa-list-alt\"></span> $lang->widgets</a>"
                        ) ,

                        array(
                            $lang->delete,
                            "<a href=\"$adminurl=\$id&action=delete\" class=\"confirm-delete-link\"><span class=\"fa fa-remove\"></span> $lang->delete</a>"
                        )
                    ));

                    $result.= $tb->build($schemes->items);
                    return $result;
                }

                $result = GetSchema::form($this->url);
                $tabs = $this->newTabs();
                $menuitems = array();
                foreach ($schemes->items as $itemview) {
                    $class = $itemview['menuclass'];
                    $menuitems[$class] = $class == 'tmenus' ? $lang->stdmenu : ($class == 'Menus' ? $lang->adminmenu : $class);
                }

                $itemview = $schemes->items[$id];
                $args->add($itemview);

                $dirlist = tfiler::getdir( $this->getApp()->paths->themes);
                sort($dirlist);
                $list = array();
                foreach ($dirlist as $dir) {
                    if (!Str::begin($dir, 'admin')) $list[$dir] = $dir;
                }

                $args->themename = $this->theme->comboItems($list, $itemview['themename']);

                $list = array();
                foreach ($dirlist as $dir) {
                    if (Str::begin($dir, 'admin')) $list[$dir] = $dir;
                }

                $args->adminname = $this->theme->comboItems($list, $itemview['adminname']);
                $args->menu = $this->theme->comboItems($menuitems, $itemview['menuclass']);
                $args->postanounce = $this->theme->comboItems(array(
                    'excerpt' => $lang->postexcerpt,
                    'card' => $lang->postcard,
                    'lite' => $lang->postlite
                ) , $itemview['postanounce']);

                $tabs->add($lang->name, '[text=name]
      [combo=themename]
      [combo=adminname]' . ($id == 1 ? '' : ('[checkbox=customsidebar] [checkbox=disableajax]')) . '[checkbox=hovermenu]
      [combo=menu]
      [combo=postanounce]
      [text=perpage]
      [checkbox=invertorder]
      ');

                $schema = Schema::i($id);
                if (count($schema->custom)) {
                    $tabs->add($lang->custom, $this->get_custom($schema));
                }

                $result.= $html->h4->help;

                $args->formtitle = $lang->edit;
                $result.= $html->adminform($tabs->get() , $args);
                break;


            case 'addview':
                $args->formtitle = $lang->addview;
                $result.= $html->adminform('[text=name]', $args);
                break;


            case 'defaults':
                $items = '';
                $theme = Theme::i();
                $tml = $theme->templates['content.admin.combo'];
                foreach ($schemes->defaults as $name => $id) {
                    $args->name = $name;
                    $args->value = static ::getcombo($id);
                    $args->data['$lang.$name'] = $lang->$name;
                    $items.= $theme->parsearg($tml, $args);
                }
                $args->items = $items;
                $args->formtitle = $lang->defaultsform;
                $result.= $theme->parsearg($theme->content->admin->form, $args);
                break;
            }

            return $html->fixquote($result);
        }

        public function processForm() {
            $result = '';
            switch ($this->name) {
                case 'views':
                    $schemes = Schemes::i();
                    $idschema = (int)$this->getparam('idschema', 0);
                    if (!$idschema || !$schemes->itemexists($idschema)) {
                        return '';
                    }

                    if ($this->action == 'delete') {
                        if ($idschema > 1) {
                            $schemes->delete($idschema);
                        }

                        return '';
                    }

                    $schema = Schema::i($idschema);
                    if ($idschema > 1) {
                        $schema->customsidebar = isset($_POST['customsidebar']);
                        $schema->disableajax = isset($_POST['disableajax']);
                    }

                    $schema->name = trim($_POST['name']);
                    $schema->themename = trim($_POST['themename']);
                    $schema->adminname = trim($_POST['adminname']);
                    $schema->menuclass = $_POST['menu'];
                    $schema->hovermenu = isset($_POST['hovermenu']);
                    $schema->postanounce = $_POST['postanounce'];
                    $schema->perpage = (int)$_POST['perpage'];
                    $schema->invertorder = isset($_POST['invertorder']);

                    $this->set_custom($idschema);
                    $schema->save();
                    break;


                case 'addview':
                    $name = trim($_POST['name']);
                    if ($name != '') {
                        $schemes = Schemes::i();
                        $id = $schemes->add($name);
                    }
                    break;


                case 'defaults':
                    $schemes = Schemes::i();
                    foreach ($schemes->defaults as $name => $id) {
                        $schemes->defaults[$name] = (int)$_POST[$name];
                    }
                    $schemes->save();
                    break;
            }

            Base::clearCache();
        }

} //class