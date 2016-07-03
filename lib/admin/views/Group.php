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
use litepubl\pages\Menu as StdMenu;
use litepubl\pages\Menus as StdMenus;
use litepubl\post\Posts;
use litepubl\utils\Filer;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Schemes as SchemaItems;

class Group extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $schemes = SchemaItems::i();
        $theme = $this->theme;
        $admin = $this->admintheme;
        $lang = Lang::i('schemes');
        $args = $this->newArgs();

        $args->formtitle = $lang->schemaposts;
        $result = $admin->form(GetSchema::combo($schemes->defaults['post'], 'postschema') . '<input type="hidden" name="action" value="posts" />', $args);

        $args->formtitle = $lang->schemamenus;
        $result.= $admin->form(GetSchema::combo($schemes->defaults['menu'], 'menuschema') . '<input type="hidden" name="action" value="menus" />', $args);

        $args->formtitle = $lang->themeschemes;
        $schema = Schema::i();

        $dirlist = Filer::getdir($this->getApp()->paths->themes);
        sort($dirlist);
        $list = array();
        foreach ($dirlist as $dir) {
            if (!Str::begin($dir, 'admin')) {
                $list[$dir] = $dir;
            }
        }

        $result.= $admin->form($theme->getinput('combo', 'themeschema', $theme->comboItems($list, $schema->themename), $lang->themename) . '<input type="hidden" name="action" value="themes" />', $args);

        return $result;
    }

    public function processForm()
    {
        switch ($_POST['action']) {
        case 'posts':
            $posts = Posts::i();
            $idschema = (int)$_POST['postview'];
            $posts->db->update("idschema = '$idschema'", 'id > 0');
            break;


        case 'menus':
            $idschema = (int)$_POST['menuview'];
            $menus = StdMenus::i();
            foreach ($menus->items as $id => $item) {
                $menu = StdMenu::i($id);
                $menu->idschema = $idschema;
                $menu->save();
            }
            break;


        case 'themes':
            $themename = $_POST['themeschema'];
            $schemes = SchemaItems::i();
            $schemes->lock();
            foreach ($schemes->items as $id => $item) {
                $schema = Schema::i($id);
                $schema->themename = $themename;
                $schema->save();
            }
            $schemes->unlock();
            break;
        }
    }
}
