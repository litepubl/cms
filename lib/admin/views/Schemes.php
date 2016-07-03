<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\admin\views;

use litepubl\admin\GetSchema;
use litepubl\core\Str;
use litepubl\utils\Filer;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Schemes as SchemaItems;
use litepubl\view\Theme;

class Schemes extends \litepubl\admin\Menu
{

    public static function replaceMenu(string $src, string $dst)
    {
        $schemes = SchemaItems::i();
        foreach ($schemes->items as & $schemaitem) {
            if ($schemaitem['menuclass'] == $src) {
                $schemaitem['menuclass'] = $dst;
            }
        }

        $schemes->save();
    }

    private function get_custom(Schema $schema)
    {
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

            $result.= $theme->getinput($customadmin[$name]['type'], "custom-$name", $value, $theme->quote($customadmin[$name]['title']));
        }

        return $result;
    }

    private function set_custom($idschema)
    {
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

    public function getContent(): string
    {
        $result = '';
        $schemes = SchemaItems::i();
        $admin = $this->adminTheme;
        $lang = Lang::i('views');
        $args = new Args();

        switch ($this->name) {
        case 'views':
            $lang->addsearch('views');

            $id = $this->getparam('idschema', 0);
            if (!$id || !$schemes->itemExists($id)) {
                $adminurl = $this->adminurl . 'schema';
                $result = $admin->h($admin->link($this->url . '/addschema/', $lang->add));

                $tb = $this->newTable();
                $tb->setStruct(
                    array(
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
                    )
                );

                $result.= $tb->build($schemes->items);
                return $result;
            }

            $result = GetSchema::form($this->url);
            $tabs = $this->newTabs();
            $menuitems = ['menu' => $lang->stdmenu, 'admin' => $lang->adminmenu, ];

            $itemview = $schemes->items[$id];
            $args->add($itemview);

            $dirlist = Filer::getDir($this->getApp()->paths->themes);
            sort($dirlist);
            $list = array();
            foreach ($dirlist as $dir) {
                if (!Str::begin($dir, 'admin')) {
                    $list[$dir] = $dir;
                }
            }

            $args->themename = $this->theme->comboItems($list, $itemview['themename']);

            $list = array();
            foreach ($dirlist as $dir) {
                if (Str::begin($dir, 'admin')) {
                    $list[$dir] = $dir;
                }
            }

            $args->adminname = $this->theme->comboItems($list, $itemview['adminname']);
            $args->postanounce = $this->theme->comboItems(
                array(
                'excerpt' => $lang->postexcerpt,
                'card' => $lang->postcard,
                'lite' => $lang->postlite
                ), $itemview['postanounce']
            );

            $args->menu = $this->theme->comboItems($menuitems, strpos($itemview['menuclass'], '\admin') ? 'admin' : 'menus');

            $tabs->add(
                $lang->name, '[text=name]
      [combo=themename]
      [combo=adminname]' . ($id == 1 ? '' : ('[checkbox=customsidebar] [checkbox=disableajax]')) . '[checkbox=hovermenu]
      [combo=menu]
      [combo=postanounce]
      [text=perpage]
      [checkbox=invertorder]
      '
            );

            $schema = Schema::i($id);
            if (count($schema->custom)) {
                $tabs->add($lang->custom, $this->get_custom($schema));
            }

            $result.= $admin->help($lang->help);

            $args->formtitle = $lang->edit;
            $result.= $admin->form($tabs->get(), $args);
            break;


        case 'addview':
        case 'addschema':
            $args->formtitle = $lang->addschema;
            $result.= $admin->form('[text=name]', $args);
            break;


        case 'defaults':
            $items = '';
            $theme = $this->theme;
            $tml = $theme->templates['content.admin.combo'];
            foreach ($schemes->defaults as $name => $id) {
                $args->name = $name;
                $args->value = GetSchema::combo($id);
                $args->data['$lang.$name'] = $lang->$name;
                $items.= $theme->parseArg($tml, $args);
            }
            $args->items = $items;
            $args->formtitle = $lang->defaultsform;
            $result.= $theme->parseArg($theme->templates['content.admin.form'], $args);
            break;
        }

        return $result;
    }

    public function processForm()
    {
        $result = '';
        switch ($this->name) {
        case 'views':
            $schemes = SchemaItems::i();
            $idschema = (int)$this->getparam('idschema', 0);
            if (!$idschema || !$schemes->itemExists($idschema)) {
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
            $schema->menuclass = $_POST['menu'] == 'admin' ? 'litepubl\admin\Menus' : 'litepubl\pages\Menus';
            $schema->hovermenu = isset($_POST['hovermenu']);
            $schema->postanounce = $_POST['postanounce'];
            $schema->perpage = (int)$_POST['perpage'];
            $schema->invertorder = isset($_POST['invertorder']);

            $this->set_custom($idschema);
            $schema->save();
            break;


        case 'addview':
        case 'addschema':
            $name = trim($_POST['name']);
            if ($name) {
                $schemes = SchemaItems::i();
                $id = $schemes->add($name);
            }
            break;


        case 'defaults':
            $schemes = SchemaItems::i();
            foreach ($schemes->defaults as $name => $id) {
                $schemes->defaults[$name] = (int)$_POST[$name];
            }
            $schemes->save();
            break;
        }

        Base::clearCache();
    }
}
