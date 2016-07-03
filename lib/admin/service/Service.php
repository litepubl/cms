<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\admin\service;

use litepubl\comments\Manager;
use litepubl\post\Posts;
use litepubl\updater\Updater;
use litepubl\view\Args;
use litepubl\view\Lang;

class Service extends Login
{

    public function getContent(): string
    {
        $admin = $this->admintheme;
        $args = new Args();
        $lang = Lang::admin('service');
        $result = $admin->h($lang->info);
        $result.= $this->doupdate($_GET);
        $tb = $this->newTable();
        $result.= $tb->props(
            array(
            'postscount' => Posts::i()->count,
            'commentscount' => Manager::i()->count,
            'version' => $this->getApp()->site->version
            )
        );

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

        return $result;
    }

    private function doUpdate($req)
    {
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

    public function processForm()
    {
        return $this->doupdate($_POST);
    }
}
