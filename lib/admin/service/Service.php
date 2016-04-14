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

class Service extends \litepubl\admin\Menu
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



                        case 'delete':
                            if ($this->confirmed) {
                                @unlink(litepubl::$paths->backup . $filename);
                                return $html->h2->backupdeleted;
                            } else {
                                $args->adminurl = $this->adminurl;
                                $args->id = $_GET['id'];
                                $args->action = 'delete';
                                $args->confirm = sprintf('%s %s?', $this->lang->confirmdelete, $_GET['id']);
                                $result.= $html->confirmform($args);
                            }
                    }
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

        $result = str_replace("'", '"', $result);
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

    public function getloginform() {
        $backuper = tbackuper::i();
        //$backuper->filertype = 'ftp';
        if ($backuper->filertype == 'file') {
            return '';
        }

        $html = $this->html;
        $args = targs::i();
        $acc = $backuper->filertype == 'ssh2' ? $html->h3->ssh2account : $html->h3->ftpaccount;
        $args->host = tadminhtml::getparam('host', '');
        $args->login = tadminhtml::getparam('login', '');
        $args->password = tadminhtml::getparam('pasword', '');
        return $acc . $html->parsearg('[text=host] [text=login] [password=password]', $args);
    }

    public function processform() {
        $html = $this->html;

        switch ($this->name) {
            case 'service':
                return $this->doupdate($_POST);
                if (!isset($_POST['sqlbackup'])) {
                    if (!$this->checkbackuper()) {
                        return $html->h3->erroraccount;
                    }
                }

                extract($_POST, EXTR_SKIP);
                $backuper = tbackuper::i();
                if (isset($restore)) {
                    if (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
                        return sprintf($html->h4red->attack, $_FILES["filename"]["name"]);
                    }

                    if (strpos($_FILES['filename']['name'], '.sql')) {
                        $backuper->uploaddump(file_get_contents($_FILES["filename"]["tmp_name"]) , $_FILES["filename"]["name"]);
                    } else {
                        $url = litepubl::$site->url;
                        $dbconfig = litepubl::$options->dbconfig;
                        $backuper->uploadarch($_FILES['filename']['tmp_name'], $backuper->getarchtype($_FILES['filename']['name']));

                        if (isset($saveurl)) {
                            $Data = new data();
                            $data->basename = 'storage';
                            $data->load();
                            $data->data['site'] = litepubl::$site->data;
                            $data->data['options']['dbconfig'] = $dbconfig;
                            $data->save();
                        }
                    }

                    ttheme::clearcache();
                    turlmap::nocache();
                    @header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                    exit();

                } elseif (isset($downloadpartial)) {
                    $filename = str_replace('.', '-', litepubl::$domain) . date('-Y-m-d') . $backuper->getfiletype();
                    $content = $backuper->getpartial(isset($plugins) , isset($theme) , isset($lib));
                    $this->sendfile($content, $filename);
                } elseif (isset($fullbackup)) {
                    $filename = str_replace('.', '-', litepubl::$domain) . date('-Y-m-d') . $backuper->getfiletype();
                    $content = $backuper->getfull();
                    $this->sendfile($content, '');
                } elseif (isset($sqlbackup)) {
                    $content = $backuper->getdump();
                    $filename = litepubl::$domain . date('-Y-m-d') . '.sql';

                    switch ($backuper->archtype) {
                        case 'tar':
                            tbackuper::include_tar();
                            $tar = new tar();
                            $tar->addstring($content, $filename, 0644);
                            $content = $tar->savetostring(true);
                            $filename.= '.tar.gz';
                            unset($tar);
                            break;


                        case 'zip':
                            tbackuper::include_zip();
                            $zip = new zipfile();
                            $zip->addFile($content, $filename);
                            $content = $zip->file();
                            $filename.= '.zip';
                            unset($zip);
                            break;


                        default:
                            $content = gzencode($content);
                            $filename.= '.gz';
                            break;
                    }

                    $this->sendfile($content, $filename);
                }
}

}