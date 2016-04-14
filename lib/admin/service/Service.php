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
$admin = $this->admintheme;
        $args = new Args();
                $lang = Lang::admin('service');
                $result= $admin->h($lang->info);
                $result.= $this->doupdate($_GET);
                $tb = $this->newTable();
                $result.= $tb->props(array(
                    'postscount' => litepubl::$classes->posts->count,
                    'commentscount' => litepubl::$classes->commentmanager->count,
                    'version' => litepubl::$site->version
                ));

                $updater = Updater::i();
                $islatest = $updater->islatest();
                if ($islatest === false) {
                    $result.= $admin->geterr($lang->errorservice);
                } elseif ($islatest <= 0) {
                    $result.= $admin->success($lang->islatest);
                } else {
                    $form = new Form($args);
                    $form->title = $lang->requireupdate;
                    $form->body = $this->getloginform() . '[submit=autoupdate]';
                    $form->submit = 'manualupdate';
                    $result.= $form->get();
                }
                break;
return $result;
    }

    private function doupdate($req) {
        $admin = $this->admintheme;
$lang = Lang::i();
        $updater = Updater::i();
        if (isset($req['autoupdate'])) {
            if (!$this->checkbackuper()) {
                return $admin->geterr($lang->erroraccount);
            }

            if ($updater->autoupdate()) {
                return $admin->success($lang->successupdated);
            }

            return $admin->h($updater->result);
        } elseif (isset($req['manualupdate'])) {
            $updater->update();
            return $admin->success($lang->successupdated);
        }
        return '';
    }

    public function processform() {
                return $this->doupdate($_POST);
}

}

