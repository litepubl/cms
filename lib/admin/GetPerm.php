<?php

namespace litepubl\admin;
use litepubl\perms\Perms;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

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

}