<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin\posts;

use litepubl\admin\Table;
use litepubl\pages\StaticPages as Pages;
use litepubl\view\Args;
use litepubl\view\Lang;

class StaticPages extends \litepubl\admin\Menu
{

    private function editForm(Args $args)
    {
        $args->text = $args->rawcontent;
        $args->formtitle = $this->title;
        return $this->admintheme->form('[text=title] [text=description] [text=keywords] [editor=text] [hidden=id]', $args);
    }

    public function getContent(): string
    {
        $result = '';
        $pages = Pages::i();
        $this->basename = 'staticpages';
        $admin = $this->admintheme;
        $lang = Lang::i('staticpages');
        $id = $this->idget();
        if (!$pages->itemExists($id)) $id = 0;
        $args = new Args();
        $args->id = $id;
        $args->adminurl = $this->adminurl;

        if ($id > 0) {
            $item = $pages->getitem($id);
            $args->add($item);
            if (isset($_GET['action']) && ($_GET['action'] == 'delete')) {
                if ($this->confirmed) {
                    $pages->delete($id);
                    $result.= $admin->success($lang->successdeleted);
                } else {
                    $result.= $this->confirmDelete($id, sprintf('%s %s?', $lang->confirmdelete, $item['title']));
                }
            } else {
                $result.= $this->editform($args);
            }
        } else {
            $args->title = '';
            $args->description = '';
            $args->keywords = '';
            $args->rawcontent = '';
            $result.= $this->editform($args);
        }

        $result.= Table::fromitems($pages->items, array(
            array(
                $lang->title,
                '<a href="$site.url$url">$title</a>'
            ) ,
            array(
                'center',
                $lang->edit,
                "<a href='$this->adminurl=\$id'>$lang->edit</a>"
            ) ,
            array(
                'center',
                $lang->delete,
                "<a href='$this->adminurl=\$id&action=delete'>$lang->delete</a>"
            ) ,
        ));

        return $result;
    }

    public function processForm()
    {
        if (empty($_POST['title'])) {
            return '';
        }

        extract($_POST);
        $pages = Pages::i();
        $id = $this->idget();
        if ($id == 0) {
            $_POST['id'] = $pages->add($title, $description, $keywords, $text);
        } else {
            $pages->edit($id, $title, $description, $keywords, $text);
        }

        return $this->admintheme->success(Lang::admin('staticpages')->success);
    }

}

