<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\files;
use litepubl\post\Files as FileItems;
use litepubl\post\MediaParser;
use litepubl\perms\Files as PrivateFiles;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Parser;
use litepubl\admin\AuthorRights;

class Thumbnails extends \litepubl\admin\Menu
{

    public function getIdfile() {
        $files = FileItems::i();
        $id = $this->idget();
        if (($id == 0) || !$files->itemExists($id)) {
 return false;
}


        if ( $this->getApp()->options->hasgroup('editor')) {
 return $id;
}


        $user =  $this->getApp()->options->user;
        $item = $files->getitem($id);
        if ($user == $item['author']) {
 return $id;
}


        return false;
    }

    public function getContent() {
        if (!($id = $this->getidfile())) {
 return $this->notfound;
}


        $result = '';
        $files = FileItems::i();
$admin = $this->admintheme;
        $lang = Lang::admin();
        $args = new Args();
        $item = $files->getitem($id);
        $idpreview = $item['preview'];
        if ($idpreview > 0) {
            $args->add($files->getitem($idpreview));
            $form = new adminform($args);
            $form->action = "$this->adminurl=$id";
            $form->inline = true;
            $form->body = $admin->help('<img src="$site.files/files/$filename" alt="thumbnail" />' . $lang->wantdelete);
            $form->submit = 'delete';
            $result.= $form->get();
        }

        $form = new adminform($args);
        $form->upload = true;
        $form->action = "$this->adminurl=$id";
        $form->title = $lang->changethumb;
        $form->body = '[upload=filename]
    [checkbox=noresize]';

        $result.= $form->get();
        return $result;
    }

    public function processForm() {
        if (!($id = $this->getidfile())) {
return $this->notfound;
}

        $files = FileItems::i();
        $item = $files->getitem($id);
$admin = $this->admintheme;
$lang = Lang::admin();

        if (isset($_POST['delete'])) {
            $files->delete($item['preview']);
            $files->setvalue($id, 'preview', 0);
            return $admintheme->success($lang->deleted);
        }

        $isauthor = 'author' ==  $this->getApp()->options->group;
        if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
return $admin->geterr(Lang::get('uploaderrors', $_FILES["filename"]["error"]));
        }

        if (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
return $admin->geterr(sprintf($lang->attack, $_FILES["filename"]["name"]));
}

        if ($isauthor && ($r = AuthorRights::i()->canupload())) {
 return $r;
}



        $filename = $_FILES['filename']['name'];
        $tempfilename = $_FILES['filename']['tmp_name'];
        $parser = MediaParser::i();
        $filename = MediaParser::linkgen($filename);
        $parts = pathinfo($filename);
        $newtemp = $parser->gettempname($parts);
        if (!move_uploaded_file($tempfilename,  $this->getApp()->paths->files . $newtemp)) {
 return sprintf($this->html->h4->attack, $_FILES["filename"]["name"]);
}



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
                PrivateFiles::i()->setperm($idpreview, (int)$item['idperm']);
            }
            return $admin->success($lang->success);
        }
    }

}