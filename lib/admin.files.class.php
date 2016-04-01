<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminfiles extends tadminmenu {

  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function getcontent() {
    $result = '';
    $files = tfiles::i();
    $admintheme = $this->admintheme;
    $lang = $this->lang;
    $args = new targs();
    if (!isset($_GET['action'])) {
      $args->add(array(
        'uploadmode' => 'file',
        'downloadurl' => '',
        'title' => '',
        'description' => '',
        'keywords' => ''
      ));

      $form = new adminform($args);
      $form->upload = true;
      $form->title = "<a id='files-source' href='#'>$lang->switchlink</a>";
      $form->items = '[upload=filename]
      [hidden=uploadmode]
      [text=downloadurl]
      [text=title]
      [text=description]
      [text=keywords]
      [checkbox=overwrite]';

      if (litepublisher::$options->show_file_perm) $form->items.= tadminperms::getcombo(0, 'idperm');
      $result.= $form->get();
    } else {
      $id = $this->idget();
      if (!$files->itemexists($id)) return $this->notfound;
      switch ($_GET['action']) {
        case 'delete':
          if ($this->confirmed) {
            if (('author' == litepublisher::$options->group) && ($r = tauthor_rights::i()->candeletefile($id))) return $r;
            $files->delete($id);
            $result.= $admintheme->success($lang->deleted);
          } else {
            $item = $files->getitem($id);
            return $this->html->confirmdelete($id, $this->adminurl, sprintf($lang->confirm, $item['filename']));
          }
          break;


        case 'edit':
          $item = $files->getitem($id);
          $args->add($item);
          $args->title = tcontentfilter::unescape($item['title']);
          $args->description = tcontentfilter::unescape($item['description']);
          $args->keywords = tcontentfilter::unescape($item['keywords']);
          $args->formtitle = $this->lang->editfile;
          $result.= $admintheme->form('[text=title] [text=description] [text=keywords]' . (litepublisher::$options->show_file_perm ? tadminperms::getcombo($item['idperm'], 'idperm') : '') , $args);
          break;
        }
    }

    $perpage = 20;
    $type = $this->name == 'files' ? '' : $this->name;
    $sql = 'parent =0';
    $sql.= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
    $sql.= $type == '' ? " and media<> 'icon'" : " and media = '$type'";
    $count = $files->db->getcount($sql);
    $from = $this->getfrom($perpage, $count);
    $list = $files->select($sql, " order by posted desc limit $from, $perpage");
    if (!$list) $list = array();
    $result.= $admintheme->getcount($count, $from, $from + count($list));

    $args->adminurl = $this->adminurl;
    $result.= tablebuilder::fromitems($files->items, array(
      array(
        'right',
        'ID',
        '$id'
      ) ,
      array(
        'right',
        $lang->filename,
        '<a href="$site.files/files/$filename">$filename</a>'
      ) ,
      array(
        $lang->image,
        $type != 'icon' ? '$title' : '<img src="$site.files/files/$filename" alt="$filename" />'
      ) ,
      array(
        $lang->edit,
        "<a href=\"$this->adminurl=\$id&action=edit\">$lang->edit</a>"
      ) ,
      array(
        $lang->thumbnail,
        '<a href="' . tadminhtml::getadminlink('/admin/files/thumbnail/', 'id=') . "\$id\" target=\"_blank\">$lang->thumbnail</a>"
      ) ,
      array(
        $lang->delete,
        "<a href=\"$this->adminurl=\$id&action=delete\" class=\"confirm-delete-link\">$lang->delete</a>"
      )
    ));

    $result.= $this->theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }

  public function processform() {
    $files = tfiles::i();
    $admintheme = $this->admintheme;
    $lang = $this->lang;

    if (empty($_GET['action'])) {
      $isauthor = 'author' == litepublisher::$options->group;
      if ($_POST['uploadmode'] == 'file') {
        if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
          return $admintheme->geterr(tlocal::get('uploaderrors', $_FILES['filename']['error']));
        }
        if (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
          return $admintheme->geterr(sprintf($lang->attack, $_FILES['filename']['name']));
        }
        if ($isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
        $overwrite = isset($_POST['overwrite']);
        $parser = tmediaparser::i();
        $id = $parser->uploadfile($_FILES['filename']['name'], $_FILES['filename']['tmp_name'], $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
      } else {
        //downloadurl
        $content = http::get($_POST['downloadurl']);
        if ($content == false) {
          return $admintheme->geterr($lang->errordownloadurl);
        }
        $filename = basename(trim($_POST['downloadurl'], '/'));
        if ($filename == '') $filename = 'noname.txt';
        if ($isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
        $overwrite = isset($_POST['overwrite']);
        $parser = tmediaparser::i();
        $id = $parser->upload($filename, $content, $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
      }

      if (isset($_POST['idperm'])) {
        tprivatefiles::i()->setperm($id, (int)$_POST['idperm']);
      }

      return $admintheme->success($lang->success);
    } elseif ($_GET['action'] == 'edit') {
      $id = $this->idget();
      if (!$files->itemexists($id)) return $this->notfound;
      $files->edit($id, $_POST['title'], $_POST['description'], $_POST['keywords']);
      if (isset($_POST['idperm'])) tprivatefiles::i()->setperm($id, (int)$_POST['idperm']);
      return $admintheme->success($lang->edited);
    }

    return '';
  }

} //class