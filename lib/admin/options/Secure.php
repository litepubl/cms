<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Parser;
use litepubl\view\Filter;
use litepubl\view\AdminParser;
use litepubl\updater\Updater;
use litepubl\updater\Backuper;
use litepubl\admin\Html;
use litepubl\admin\Form;
use litepubl\admin\Menus;

class Secure extends \litepubl\admin\Menu
{

    public function getcontent() {
        $options = litepubl::$options;
        $lang = tlocal::admin('options');
        $args = new Args();
        $args->echoexception = $options->echoexception;
        $args->usersenabled = $options->usersenabled;
        $args->reguser = $options->reguser;
        $args->parsepost = $options->parsepost;
        $args->show_draft_post = $options->show_draft_post;
        $args->xxxcheck = $options->xxxcheck;

        $filter = Filter::i();
        $args->phpcode = $filter->phpcode;

        $parser = Parser::i();
        $args->removephp = $parser->removephp;
        $args->removespaces = $parser->removespaces;

        $args->useshell = Updater::i()->useshell;
        $backuper = Backuper::i();
        $args->filertype = Html::array2combo(array(
            'auto' => 'auto',
            'file' => 'file',
            'ftp' => 'ftp',
            'ftpsocket' => 'ftpsocket',
            //'ssh2' => 'ssh2'
            
        ) , $backuper->filertype);

        $args->formtitle = $lang->securehead;
        $result = $this->admintheme->form('
      [checkbox=echoexception]
      [checkbox=xxxcheck]
      [checkbox=usersenabled]
      [checkbox=reguser]
      [checkbox=removephp]
      [checkbox=removespaces]
      [checkbox=phpcode]
      [checkbox=parsepost]
      [checkbox=show_draft_post]
      [combo=filertype]
      [checkbox=useshell]
      ', $args);

        $form = new Form($args);
        $form->title = $lang->changepassword;
        $args->oldpassword = '';
        $args->newpassword = '';
        $args->repassword = '';
        $form->body = '[password=oldpassword]
      [password=newpassword]
      [password=repassword]';

        $form->submit = 'changepassword';
        $result.= $form->get();
        return $result;
    }

    public function processform() {
        extract($_POST, EXTR_SKIP);
        $options = litepubl::$options;
$admin = $this->admintheme;

        if (isset($_POST['oldpassword'])) {
            $h4 = $this->html->h4;
            if ($oldpassword == '') {
                return $h4->badpassword;
            }

            if (($newpassword == '') || ($newpassword != $repassword)) {
                return $h4->difpassword;
            }

            if (!$options->auth($options->email, $oldpassword)) {
                return $h4->badpassword;
            }

            $options->changepassword($newpassword);
            $options->logout();
            return $h4->passwordchanged;
        }

        $options->echoexception = isset($echoexception);
        $options->reguser = isset($reguser);
        $this->setusersenabled(isset($usersenabled));
        $options->parsepost = isset($parsepost);
        $options->show_draft_post = isset($show_draft_post);
        $options->xxxcheck = isset($xxxcheck);

        $filter = Filter::i();
        $filter->phpcode = isset($phpcode);
        $filter->save();

        $parser = Parser::i();
        $parser->removephp = isset($removephp);
        $parser->removespaces = isset($removespaces);
        $parser->save();

        $parser = AdminParser::i();
        $parser->removephp = isset($removephp);
        $parser->removespaces = isset($removespaces);
        $parser->save();

        $backuper = Backuper::i();
        if ($backuper->filertype != $filertype) {
            $backuper->filertype = $filertype;
            $backuper->save();
        }

        $useshell = isset($useshell);
        $updater = Updater::i();
        if ($useshell !== $updater->useshell) {
            $updater->useshell = $useshell;
            $updater->save();
        }
    }

    public function setusersenabled($value) {
        if (litepubl::$options->usersenabled == $value) {
            return;
        }

        litepubl::$options->usersenabled = $value;
        $menus = Menus::i();
        $menus->lock();
        if ($value) {
            if (!$menus->url2id('/admin/users/')) {
                $id = $menus->createitem(0, 'users', 'admin', 'tadminusers');
                $menus->createitem($id, 'pages', 'author', 'tadminuserpages');
                $menus->createitem($id, 'groups', 'admin', 'tadmingroups');
                $menus->createitem($id, 'options', 'admin', 'tadminuseroptions');
                $menus->createitem($id, 'perms', 'admin', 'tadminperms');
                $menus->createitem($id, 'search', 'admin', 'tadminusersearch');

                $menus->createitem($menus->url2id('/admin/posts/') , 'authorpage', 'author', 'tadminuserpages');
            }
        } else {
            $menus->deletetree($menus->url2id('/admin/users/'));
            $menus->deleteurl('/admin/posts/authorpage/');
        }
        $menus->unlock();
    }

}