<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\service;
use litepubl\core\Data;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\updater\Updater;

class Service extends Login
{

    public function getcontent() {
        $result = '';
        $args = new Args();

        switch ($this->name) {
            case 'service':
                if (!dbversion) {
                    return $html->h2->noupdates;
                }

                $lang = $this->lang;
                $result.= $html->h3->info;
                $result.= $this->doupdate($_GET);
                $tb = new tablebuilder();
                $result.= $tb->props(array(
                    'postscount' => litepubl::$classes->posts->count,
                    'commentscount' => litepubl::$classes->commentmanager->count,
                    'version' => litepubl::$site->version
                ));
                $updater = tupdater::i();
                $islatest = $updater->islatest();
                if ($islatest === false) {
                    $result.= $html->h4->errorservice;
                } elseif ($islatest <= 0) {
                    $result.= $html->h4->islatest;
                } else {
                    $form = new adminform($args);
                    $form->title = tlocal::i()->requireupdate;
                    $form->items = $this->getloginform() . '[submit=autoupdate]';
                    $form->submit = 'manualupdate';
                    $result.= $form->get();
                }
                break;


            case 'upload':
                $args->url = str_replace('$mysite', rawurlencode(litepubl::$site->url) , tadminhtml::getparam('url', ''));
                $lang = tlocal::admin();
                $form = new adminform($args);
                $form->title = $lang->uploaditem;
                $form->upload = true;
                $form->items = '[text=url]
      [upload=filename]' . $this->getloginform();
                $result = $html->p->uploaditems;
                $result.= $form->get();
                break;
        }

        return $result;
    }

    private function doupdate($req) {
        $html = $this->html;
        $updater = tupdater::i();
        if (isset($req['autoupdate'])) {
            if (!$this->checkbackuper()) {
                return $html->h4->erroraccount;
            }

            if ($updater->autoupdate()) {
                return $html->h4->successupdated;
            }

            return sprintf('<h3>%s</h3>', $updater->result);
        } elseif (isset($req['manualupdate'])) {
            $updater->update();
            return $html->h4->successupdated;
        }
        return '';
    }

    public function checkbackuper() {
        $backuper = tbackuper::i();
        if ($backuper->filertype == 'file') {
            return true;
        }

        $host = tadminhtml::getparam('host', '');
        $login = tadminhtml::getparam('login', '');
        $password = tadminhtml::getparam('password', '');
        if (($host == '') || ($login == '') || ($password == '')) {
            return '';
        }

        return $backuper->connect($host, $login, $password);
    }

    public function processform() {
        $html = $this->html;

        switch ($this->name) {
            case 'service':
                return $this->doupdate($_POST);


            case 'upload':
                $backuper = tbackuper::i();
                if (!$this->checkbackuper()) {
                    return $html->h3->erroraccount;
                }

                if (is_uploaded_file($_FILES['filename']['tmp_name']) && !(isset($_FILES['filename']['error']) && ($_FILES['filename']['error'] > 0))) {
                    $result = $backuper->uploadarch($_FILES['filename']['tmp_name'], $backuper->getarchtype($_FILES['filename']['name']));
                } else {
                    $url = trim($_POST['url']);
                    if (empty($url)) {
                        return '';
                    }

                    if (!($s = http::get($url))) {
                        return $html->h3->errordownload;
                    }

                    $archtype = $backuper->getarchtype($url);
                    if (!$archtype) {
                        //         local file header signature     4 bytes  (0x04034b50)
                        $archtype = strbegin($s, "\x50\x4b\x03\x04") ? 'zip' : 'tar';
                    }

                    if (($archtype == 'zip') && class_exists('zipArchive')) {
                        $filename = litepubl::$paths->storage . 'backup/temp.zip';
                        file_put_contents($filename, $s);
                        @chmod($filename, 0666);
                        $s = '';
                        $result = $backuper->uploadzip($filename);
                        @unlink($filename);
                    } else {
                        $result = $backuper->upload($s, $archtype);
                    }
                }

                if ($result) {
                    return $html->h3->itemuploaded;
                } else {
                    return sprintf('<h3>%s</h3>', $backuper->result);
                }
                break;
        }

    }

}