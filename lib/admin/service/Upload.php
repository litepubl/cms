<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\admin\service;

use litepubl\core\Str;
use litepubl\updater\Backuper;
use litepubl\view\Lang;

class Upload extends Login
{

    public function getContent(): string
    {
        $admin = $this->admintheme;
        $lang = Lang::admin('service');
        $args = $this->newArgs();

        $args->url = str_replace('$mysite', rawurlencode($this->getApp()->site->url), $this->getparam('url', ''));
        $lang = Lang::admin();
        $form = $this->newForm();
        $form->title = $lang->uploaditem;
        $form->upload = true;
        $form->body = '[text=url]
      [upload=filename]';

        $form->body.= $this->getloginform();
        $form->body.= $admin->help($lang->uploaditems);
        return $form->get();
    }

    public function processForm()
    {
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
                $archtype = Str::begin($s, "\x50\x4b\x03\x04") ? 'zip' : 'tar';
            }

            $result = $backuper->upload($s, $archtype);
        }

        if ($result) {
            return $admin->success($lang->itemuploaded);
        } else {
            return $admin->h($backuper->result);
        }
    }
}
