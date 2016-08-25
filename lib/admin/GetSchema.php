<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\admin;

use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Theme;

class GetSchema
{
    use \litepubl\core\AppTrait;

    public static function form($url)
    {
        Lang::admin();
        $args = new Args();
        $id = !empty($_GET['idschema']) ? (int)$_GET['idschema'] : (!empty($_POST['idschema']) ? (int)$_POST['idschema'] : 0);
        $args->idschema = static ::items($id);
        $form = new Form($args);
        $form->action = static ::getAppInstance()->site->url . $url;
        $form->inline = true;
        $form->method = 'get';
        $form->body = '[combo=idschema]';
        $form->submit = 'select';
        return $form->get();
    }

    public static function combo($idschema, $name = 'idschema')
    {
        $lang = Lang::admin();
        $lang->addsearch('views');
        $theme = Theme::i();
        return strtr(
            $theme->templates['content.admin.combo'], [
            '$lang.$name' => $lang->schema,
            '$name' => $name,
            '$value' => static ::items($idschema)
            ]
        );
    }

    public static function items($idschema)
    {
        $result = '';
        $schemes = schemes::i();
        foreach ($schemes->items as $id => $item) {
            $result.= sprintf('<option value="%d" %s>%s</option>', $id, $idschema == $id ? 'selected="selected"' : '', $item['name']);
        }

        return $result;
    }
}
