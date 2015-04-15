<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfilethumbnails extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getidfile() {
    $files = tfiles::i();
    $id = $this->idget();
    if (($id == 0) || !$files->itemexists($id)) return false;
    if (litepublisher::$options->hasgroup('editor')) return $id;
    $user = litepublisher::$options->user;
    $item = $files->getitem($id);
    if ($user == $item['author']) return $id;
    return false;
  }
  
  public function getcontent() {
    if (!($id = $this->getidfile()))   return $this->notfound;
    $result = '';
    $files = tfiles::i();
    $html = $this->html;
    $lang = tlocal::admin();
    $args = new targs();
    $item = $files->getitem($id);
    $idpreview = $item['preview'];
    if ($idpreview > 0) {
      $args->add($files->getitem($idpreview));
      $form = new  adminform($args);
      $form->action = "$this->adminurl=$id";
      $form->inline = true;
      $form->items = $html->p('<img src="$site.files/files/$filename" alt="thumbnail" />' . $lang->wantdelete);
      $form->submit = 'delete';
      $result .= $form->get();
    }
    
    $form = new  adminform($args);
    $form->upload = true;
    $form->action = "$this->adminurl=$id";
    $form->title = $lang->changethumb;
    $form->items = '[upload=filename]
    [checkbox=noresize]';
    
    $result .= $form->get();
    return $result;
  }
  
  public function processform() {
    if (!($id = $this->getidfile()))   return $this->notfound;
    $files = tfiles::i();
    $item = $files->getitem($id);
    
    if (isset($_POST['delete'])) {
      $files->delete($item['preview']);
      $files->setvalue($id, 'preview', 0);
      return $this->html->h4->deleted;
    }
    
    $isauthor = 'author' == litepublisher::$options->group;
    if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
      $error = tlocal::get('uploaderrors', $_FILES["filename"]["error"]);
      return "<h3>$error</h3>\n";
    }
    
    if (!is_uploaded_file($_FILES['filename']['tmp_name'])) return sprintf($this->html->h4red->attack, $_FILES["filename"]["name"]);
    if ($isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
    
    $filename = $_FILES['filename']['name'];
    $tempfilename = $_FILES['filename']['tmp_name'];
    $parser = tmediaparser::i();
    $filename = tmediaparser::linkgen($filename);
    $parts = pathinfo($filename);
    $newtemp = $parser->gettempname($parts);
    if (!move_uploaded_file($tempfilename, litepublisher::$paths->files . $newtemp)) return sprintf($this->html->h4->attack, $_FILES["filename"]["name"]);
    
    $resize = !isset($_POST['noresize']);
    
    $idpreview = $parser->add(array(
    'filename' => $filename,
    'tempfilename' => $newtemp,
    'enabledpreview' => $resize,
    'ispreview' => $resize
    ));
    
    if ($idpreview) {
      if ($item['preview'] > 0) $files->delete($item['preview']);
      $files->setvalue($id, 'preview', $idpreview);
      $files->setvalue($idpreview, 'parent', $id);
      if ($item['idperm'] > 0) {
        $files->setvalue($idpreview, 'idperm', $item['idperm']);
        tprivatefiles::i()->setperm($idpreview, (int) $item['idperm']);
      }
      return $this->html->h4->success;
    }
  }
  
}//class