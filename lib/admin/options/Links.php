<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\options;

use litepubl\utils\LinkGenerator;
use litepubl\view\Lang;

class Links extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $lang = Lang::admin('options');
        $admin = $this->admintheme;
        $args = $this->newArgs();

        $linkgen = LinkGenerator::i();
        $args->urlencode = $linkgen->urlencode;
        $args->post = $linkgen->post;
        $args->menu = $linkgen->menu;
        $args->category = $linkgen->category;
        $args->tag = $linkgen->tag;
        $args->archive = $linkgen->archive;

        $args->formtitle = $lang->schemalinks;
        return $admin->form(
            $admin->help($lang->taglinks) . '[checkbox=urlencode]
      [text=post]
      [text=menu]
      [text=category]
      [text=tag]
      [text=archive]
      ', $args
        );
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $linkgen = LinkGenerator::i();
        $linkgen->urlencode = isset($urlencode);
        if (!empty($post)) {
            $linkgen->post = $post;
        }

        if (!empty($menu)) {
            $linkgen->menu = $menu;
        }

        if (!empty($category)) {
            $linkgen->category = $category;
        }

        if (!empty($tag)) {
            $linkgen->tag = $tag;
        }

        if (!empty($archive)) {
            $linkgen->archive = $archive;
        }

        $linkgen->save();
    }
}
