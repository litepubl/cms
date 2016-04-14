<?php

namespace litepubl\admin\service;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\updater\Backuper;
use litepubl\utils\Filer;
use litepubl\admin\Form;

class Backup extends Login
{
public function getcontent() {
$admin = $this->admintheme;
$lang = Lang::admin('service');
$args = new Args();

                if (empty($_GET['action'])) {
                    $args->plugins = false;
                    $args->theme = false;
                    $args->lib = false;
                    $args->dbversion = '';
                    $args->saveurl = true;

                    $form = new Form($args);
                    $form->upload = true;
                    $form->body = $admin->h($lang->partialform);
                    $form->body .= $this->getloginform();
                    $form->body .= '[checkbox=plugins]
        [checkbox=theme]
        [checkbox=lib]
        [submit=downloadpartial]';

                    $form->body .= $admin->help($lang->notefullbackup);
                    $form->body .= '[submit=fullbackup]
        [submit=sqlbackup]';

                    $form->body .= $admin->h($lang->uploadhead);
                    $form->body .= '[upload=filename]
        [checkbox=saveurl]';

                    $form->submit = 'restore';
                    $result = $form->get();
                    $result.= $this->getbackupfilelist();
                } else {
                    $filename = $_GET['id'];
                    if (strpbrk($filename, '/\<>')) {
                        return $this->notfound;
                    }

                    if (!file_exists(litepubl::$paths->backup . $filename)) {
                        return $this->notfound;
                    }

                    switch ($_GET['action']) {
                        case 'download':
                            if ($s = @file_get_contents(litepubl::$paths->backup . $filename)) {
                                $this->sendfile($s, $filename);
                            } else {
                                return $this->notfound;
                            }
                            break;


                        case 'delete':
                            if ($this->confirmed) {
                                @unlink(litepubl::$paths->backup . $filename);
                                return $admin->succes($lang->backupdeleted);
                            } else {
                                $args->adminurl = $this->adminurl;
                                $args->id = $_GET['id'];
                                $args->action = 'delete';
                                $args->confirm = sprintf('%s %s?', $lang->confirmdelete, $_GET['id']);
                                $result.= $html->confirmform($args);
                            }
                    }
                }
}

public function processform() {
$admin = $this->admintheme;
                if (!isset($_POST['sqlbackup'])) {
                    if (!$this->checkbackuper()) {
                        return $admin->geterr($lang->erroraccount);
                    }
                }

                extract($_POST, EXTR_SKIP);
                $backuper = Backuper::i();
                if (isset($restore)) {
                    if (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
                        return $admin->geterr(sprintf($lang->attack, $_FILES["filename"]["name"]));
                    }

                    if (strpos($_FILES['filename']['name'], '.sql')) {
                        $backuper->uploaddump(file_get_contents($_FILES["filename"]["tmp_name"]) , $_FILES["filename"]["name"]);
                    } else {
                        $url = litepubl::$site->url;
                        $dbconfig = litepubl::$options->dbconfig;
                        $backuper->uploadarch($_FILES['filename']['tmp_name'], $backuper->getarchtype($_FILES['filename']['name']));

                        if (isset($saveurl)) {
                            $data = new Data();
                            $data->basename = 'storage';
                            $data->load();
                            $data->data['site'] = litepubl::$site->data;
                            $data->data['options']['dbconfig'] = $dbconfig;
                            $data->save();
                        }
                    }

                    $admin->clearcache();
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
                            $tar = $backuper->newTar();
                            $tar->addstring($content, $filename, 0644);
                            $content = $tar->savetostring(true);
                            $filename.= '.tar.gz';
                            unset($tar);
                            break;


                        case 'zip':
$tempfile = litepubl::$paths->backup . md5rand() . '.zip';
                            $zip = new \ZipArchive();
if ($zip->open($tempfile, \ZipArchive::CREATE) === true) {
                            $zip->addFromString($filename, $content);
$zip->close();
                            unset($zip);

                            $content = file_get_contents($tempfile);
@unlink($tempfile);
                            $filename.= '.zip';
}
                            break;


                        default:
                            $content = gzencode($content);
                            $filename.= '.gz';
                            break;
                    }

                    $this->sendfile($content, $filename);
                }
}

    private function sendfile(&$content, $filename) {
        //@file_put_contents(litepubl::$domain . ".zip", $content);
        if (!$filename) {
$filename = str_replace('.', '-', litepubl::$domain) . date('-Y-m-d') . '.zip';
}

        if (ob_get_level()) {
ob_end_clean();
}

        header('HTTP/1.1 200 OK', true, 200);
        Header('Cache-Control: no-cache, must-revalidate');
        Header('Pragma: no-cache');
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . strlen($content));
        header('Last-Modified: ' . date('r'));

        echo $content;
        exit();
    }

    private function getbackupfilelist() {
        $list = tfiler::getfiles(litepubl::$paths->backup);
        if (!count($list)) {
            return '';
        }

        $items = array();
        $html = $this->html;
        foreach ($list as $filename) {
            if (strend($filename, '.gz') || strend($filename, '.zip')) {
                $items[]['filename'] = $filename;
            }
        }

        if (!count($items)) {
            return '';
        }

        $lang = $this->lang;
        return $this->html->h4->backupheadern . $this->html->buildtable($items, array(
            array(
                'right',
                $lang->download,
                "<a href=\"$this->adminurl=\$filename&action=download\">\$filename</a>"
            ) ,
            array(
                'right',
                $lang->delete,
                "<a href=\"$this->adminurl=\$filename&action=delete\">$lang->delete</a>"
            )
        ));
    }

}