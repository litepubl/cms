<?php

namespace litepubl\admin\service;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\updater\Backuper;

class Backup extends \litepubl\admin\Menu
{
public function getcontent() {
$lang = Lang::admin('service');
$args = new Args();
                if (empty($_GET['action'])) {

                    $args->plugins = false;
                    $args->theme = false;
                    $args->lib = false;
                    $args->dbversion = dbversion ? '' : 'disabled="disabled"';
                    $args->saveurl = true;

                    $form = new adminform($args);
                    $form->upload = true;
                    $form->items = $html->h4->partialform;
                    $form->items.= $this->getloginform();
                    $form->items.= '[checkbox=plugins]
        [checkbox=theme]
        [checkbox=lib]
        [submit=downloadpartial]';

                    $form->items.= $html->p->notefullbackup;
                    $form->items.= '[submit=fullbackup]
        [submit=sqlbackup]';

                    $form->items.= $html->h4->uploadhead;
                    $form->items.= '[upload=filename]
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
}

public function processform() {
