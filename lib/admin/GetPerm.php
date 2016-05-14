<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin;

use litepubl\core\UserGroups;
use litepubl\perms\Perms;
use litepubl\view\Admin;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class GetPerm
{

    public static function combo($idperm, $name = 'idperm')
    {
        $lang = Lang::admin();
        $section = $lang->section;
        $lang->section = 'perms';
        $theme = Theme::i();
        $result = strtr($theme->templates['content.admin.combo'], array(
            '$lang.$name' => $lang->perm,
            '$name' => $name,
            '$value' => static ::items($idperm)
        ));

        $lang->section = $section;
        return $result;
    }

    public static function items($idperm)
    {
        $result = sprintf('<option value="0" %s>%s</option>', $idperm == 0 ? 'selected="selected"' : '', Lang::get('perms', 'nolimits'));
        $perms = Perms::i();
        foreach ($perms->items as $id => $item) {
            $result.= sprintf('<option value="%d" %s>%s</option>', $id, $idperm == $id ? 'selected="selected"' : '', $item['name']);
        }

        return $result;
    }

    public static function groups(array $idgroups)
    {
        $groups = UserGroups::i();
        $admin = Admin::admin();
        $tml = $admin->templates['checkbox.label'];
        $ulist = new UList($admin);
        $ulist->value = $ulist->link;

        $args = new Args();
        foreach ($groups->items as $id => $item) {
            $args->add($item);
            $args->id = $id;
            $args->name = 'idgroup';
            $args->checked = in_array($id, $idgroups);
            $ulist->add(0, strtr($tml, $args->data));
        }

        return $ulist->getresult();
    }

}

