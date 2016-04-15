<?php

namespace litepubl\admin;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class GetSchema
 {

    public static function form($url) {
        $lang = tlocal::admin();
        $args = new Args();
        $args->idschema = static ::items(tadminhtml::getparam('idview', 1));
        $form = new Form($args);
        $form->action = litepubl::$site->url . $url;
        $form->inline = true;
        $form->method = 'get';
        $form->body = '[combo=idschema]';
        $form->submit = 'select';
        return $form->get();
    }

    public static function combo($idschema, $name = 'idschema') {
        $lang = Lang::i();
        $lang->addsearch('views');
        $theme = Theme::i();
        return strtr($theme->templates['content.admin.combo'], array(
            '$lang.$name' => $lang->schema,
            '$name' => $name,
            '$value' => static ::items($idschema)
        ));
    }

    public static function items($idschema) {
        $result = '';
        $schemes = schemes ::i();
        foreach ($schemes->items as $id => $item) {
            $result.= sprintf('<option value="%d" %s>%s</option>', $id, $idschema == $id ? 'selected="selected"' : '', $item['name']);
        }

        return $result;
    }

}