<?php


namespace litepubl\admin\service;
use litepubl\view\Lang;
class Upload extends Login
{

public function getcontent() {
$lang = Lang::admin('service');
$args = $this->newArgs();


                $args->url = str_replace('$mysite', rawurlencode(litepubl::$site->url) , $this->getparam('url', ''));
                $lang = tlocal::admin();
                $form = new adminform($args);
                $form->title = $lang->uploaditem;
                $form->upload = true;
                $form->items = '[text=url]
      [upload=filename]' . $this->getloginform();
                $result = $html->p->uploaditems;
return $form->get();
}
public function processform() {
$admin = $this->admintheme;
$lang = Lang::admin('service');
                $backuper = Backuper::i();
                if (!$this->checkbackuper()) {
                    return $admin->geterr($lang->erroraccount);
                }

                if (is_uploaded_file($_FILES['filename']['tmp_name']) && !(isset($_FILES['filename']['error']) && ($_FILES['filename']['error'] > 0))) {
                    $result = $backuper->uploadarch($_FILES['filename']['tmp_name'], $backuper->getarchtype($_FILES['filename']['name']));
                } else {
                    $url = trim($_POST['url']);
                    if (empty($url)) {
                        return '';
                    }

                    if (!($s = http::get($url))) {
                        return $admin->geterr($lang->errordownload);
                    }

                    $archtype = $backuper->getarchtype($url);
                    if (!$archtype) {
                        //         local file header signature     4 bytes  (0x04034b50)
                        $archtype = strbegin($s, "\x50\x4b\x03\x04") ? 'zip' : 'tar';
                    }

                        $result = $backuper->upload($s, $archtype);
                }

                if ($result) {
                    return $admin->success($lang->itemuploaded);
                } else {
                    return $admin->h($backuper->result);
                }
                break;
    }

}