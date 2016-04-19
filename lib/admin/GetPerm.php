<?php

namespace litepubl\admin;
use litepubl\perms\Perms;
use litepubl\core\UserGroups;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Admin;

class GetPerm
 {

    public static function combo($idperm, $name = 'idperm') {
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

    public static function items($idperm) {
        $result = sprintf('<option value="0" %s>%s</option>', $idperm == 0 ? 'selected="selected"' : '', tlocal::get('perms', 'nolimits'));
        $perms = Perms::i();
        foreach ($perms->items as $id => $item) {
            $result.= sprintf('<option value="%d" %s>%s</option>', $id, $idperm == $id ? 'selected="selected"' : '', $item['name']);
        }

        return $result;
    }

    public static function groups(array $idgroups) {
        $groups = UserGroups::i();
$admin = Admin::admin();
$tml =$admin->templates['checkbox.label'];
$ulist = new UList($admin);
$ulist->value = $ulist->link;

        $args = new targs();
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