<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\views;
use litepubl\view\Schemes as SchemaItems;
use litepubl\view\Schema;
use litepubl\post\Posts;
use litepubl\pages\Menus as StdMenus;
use litepubl\pages\Menu as StdMenu;
use litepubl\admin\GetSchema;
use litepubl\core\Str;
use litepubl\view\Lang;

class Group extends \litepubl\admin\Menu
{

    public function getContent() {
        $schemes = SchemaItems::i();
$theme = $this->theme;
$admin = $this->admin;
        $lang = Lang::i('schemes');
        $args = $this->newArgs();

        $args->formtitle = $lang->viewposts;
        $result = $admin->form(GetSchema::combo($schemes->defaults['post'], 'postview') . '<input type="hidden" name="action" value="posts" />', $args);

        $args->formtitle = $lang->viewmenus;
        $result.= $admin->form(GetSchema::combo($schemes->defaults['menu'], 'menuview') . '<input type="hidden" name="action" value="menus" />', $args);

        $args->formtitle = $lang->themeviews;
        $schema = Schema::i();

        $dirlist = Filer::getdir( $this->getApp()->paths->themes);
        sort($dirlist);
        $list = array();
        foreach ($dirlist as $dir) {
            if (!Str::begin($dir, 'admin')) {
$list[$dir] = $dir;
}
        }

        $result.= $admin->form(
$theme->getinput('combo', 'themeview', $theme->comboItems($list, $schema->themename) , $lang->themename) .
 '<input type="hidden" name="action" value="themes" />', $args);

return $result;
    }

    public function processForm() {
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
                $themename = $_POST['themeview'];
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