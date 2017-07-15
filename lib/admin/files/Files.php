<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\files;

use litepubl\admin\AuthorRights;
use litepubl\admin\Form;
use litepubl\admin\GetPerm;
use litepubl\admin\Link;
use litepubl\perms\Files as PrivateFiles;
use litepubl\post\Files as FileItems;
use litepubl\post\MediaParser;
use litepubl\utils\http;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Parser;

class Files extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $files = FileItems::i();
        $admintheme = $this->admintheme;
        $lang = $this->lang;
        $args = new Args();
        if (!isset($_GET['action'])) {
            $args->add(
                [
                'uploadmode' => 'file',
                'downloadurl' => '',
                'title' => '',
                'description' => '',
                'keywords' => ''
                ]
            );

            $form = new Form($args);
            $form->upload = true;
            $form->title = "<a id='files-source' href='#'>$lang->switchlink</a>";
            $form->body = '[upload=filename]
      [hidden=uploadmode]
      [text=downloadurl]
      [text=title]
      [text=description]
      [text=keywords]
      [checkbox=overwrite]';

            if ($this->getApp()->options->show_file_perm) {
                $form->items.= GetPerm::combo(0, 'idperm');
            }
            $result.= $form->get();
        } else {
            $id = $this->idget();
            if (!$files->itemExists($id)) {
                return $this->notfound;
            }

            switch ($_GET['action']) {
                case 'delete':
                    if ($this->confirmed) {
                        if (('author' == $this->getApp()->options->group) && !AuthorRights::canDeleteFile($id)) {
                            return AuthorRights::getMessage();
                        }

                        $files->delete($id);
                        $result.= $admintheme->success($lang->deleted);
                    } else {
                        $item = $files->getitem($id);
                        return $this->confirmDelete($id, sprintf($lang->confirm, $item['filename']));
                    }
                    break;


                case 'edit':
                    $item = $files->getitem($id);
                    $args->add($item);
                    $args->title = Filter::unescape($item['title']);
                    $args->description = Filter::unescape($item['description']);
                    $args->keywords = Filter::unescape($item['keywords']);
                    $args->formtitle = $this->lang->editfile;
                    $result.= $admintheme->form('[text=title] [text=description] [text=keywords]' . ($this->getApp()->options->show_file_perm ? AdminPerms::getcombo($item['idperm'], 'idperm') : ''), $args);
                    break;
            }
        }

        $perpage = 20;
        $type = $this->name == 'files' ? '' : $this->name;
        $sql = 'parent =0';
        $sql.= $this->getApp()->options->user <= 1 ? '' : ' and author = ' . $this->getApp()->options->user;
        $sql.= $type == '' ? " and media<> 'icon'" : " and media = '$type'";
        $count = $files->db->getcount($sql);
        $from = $this->getfrom($perpage, $count);
        $list = $files->select($sql, " order by posted desc limit $from, $perpage");
        if (!$list) {
            $list = [];
        }
        $result.= $admintheme->getcount($count, $from, $from + count($list));

        $args->adminurl = $this->adminurl;
        $result.= $this->tableItems(
            $files->items,
            [
            [
                'right',
                'ID',
                '$id'
            ] ,
            [
                'right',
                $lang->filename,
                '<a href="$site.files/files/$filename">$filename</a>'
            ] ,
            [
                $lang->image,
                $type != 'icon' ? '$title' : '<img src="$site.files/files/$filename" alt="$filename" />'
            ] ,
            [
                $lang->edit,
                "<a href=\"$this->adminurl=\$id&action=edit\">$lang->edit</a>"
            ] ,
            [
                $lang->thumbnail,
                '<a href="' . Link::url('/admin/files/thumbnail/?id=') . "\$id\" target=\"_blank\">$lang->thumbnail</a>"
            ] ,
            [
                $lang->delete,
                "<a href=\"$this->adminurl=\$id&action=delete\" class=\"confirm-delete-link\">$lang->delete</a>"
            ]
            ]
        );

        $result.= $this->theme->getpages($this->url, $this->getApp()->context->request->page, ceil($count / $perpage));
        return $result;
    }

    public function processForm()
    {
        $files = FileItems::i();
        $admintheme = $this->admintheme;
        $lang = $this->lang;

        if (empty($_GET['action'])) {
            $isauthor = 'author' == $this->getApp()->options->group;
            if ($_POST['uploadmode'] == 'file') {
                if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
                    return $admintheme->geterr(Lang::get('uploaderrors', $_FILES['filename']['error']));
                }
                if (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
                    return $admintheme->geterr(sprintf($lang->attack, $_FILES['filename']['name']));
                }
                if ($isauthor && !AuthorRights::canUpload()) {
                    return AuthorRights::getMessage();
                }

                $overwrite = isset($_POST['overwrite']);
                $parser = MediaParser::i();
                $id = $parser->uploadfile($_FILES['filename']['name'], $_FILES['filename']['tmp_name'], $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
            } else {
                //downloadurl
                $content = http::get($_POST['downloadurl']);
                if ($content == false) {
                    return $admintheme->geterr($lang->errordownloadurl);
                }
                $filename = basename(trim($_POST['downloadurl'], '/'));
                if ($filename == '') {
                    $filename = 'noname.txt';
                }
                if ($isauthor && !AuthorRights::canUpload()) {
                    return AuthorRights::getMessage();
                }
                $overwrite = isset($_POST['overwrite']);
                $parser = MediaParser::i();
                $id = $parser->upload($filename, $content, $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
            }

            if (isset($_POST['idperm'])) {
                PrivateFiles::i()->setperm($id, (int)$_POST['idperm']);
            }

            return $admintheme->success($lang->success);
        } elseif ($_GET['action'] == 'edit') {
            $id = $this->idget();
            if (!$files->itemExists($id)) {
                return $this->notfound;
            }

            $files->edit($id, $_POST['title'], $_POST['description'], $_POST['keywords']);
            if (isset($_POST['idperm'])) {
                PrivateFiles::i()->setperm($id, (int)$_POST['idperm']);
            }

            return $admintheme->success($lang->edited);
        }

        return '';
    }
}
