<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class adminsecure extends tadminmenu {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function getcontent() {
        $options = litepubl::$options;
        $lang = tlocal::admin('options');
        $html = $this->gethtml('options');
        $args = new targs();
        $args->echoexception = $options->echoexception;
        $args->usersenabled = $options->usersenabled;
        $args->reguser = $options->reguser;
        $args->parsepost = $options->parsepost;
        $args->show_draft_post = $options->show_draft_post;
        $args->xxxcheck = $options->xxxcheck;
        $filter = tcontentfilter::i();
        $args->phpcode = $filter->phpcode;
        $parser = tthemeparser::i();
        $args->removephp = $parser->removephp;
        $args->removespaces = $parser->removespaces;

        $args->useshell = tupdater::i()->useshell;
        $backuper = tbackuper::i();
        $args->filertype = tadminhtml::array2combo(array(
            'auto' => 'auto',
            'file' => 'file',
            'ftp' => 'ftp',
            'ftpsocket' => 'ftpsocket',
            //'ssh2' => 'ssh2'
            
        ) , $backuper->filertype);

        $args->formtitle = $lang->securehead;
        $result = $html->adminform('
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

        $form = new adminform($args);
        $form->title = $lang->changepassword;
        $args->oldpassword = '';
        $args->newpassword = '';
        $args->repassword = '';
        $form->items = '[password=oldpassword]
      [password=newpassword]
      [password=repassword]';

        $form->submit = 'changepassword';
        $result.= $form->get();
        return $result;
    }

    public function processform() {
        $options = litepubl::$options;
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
        $filter = tcontentfilter::i();
        $filter->phpcode = isset($phpcode);
        $filter->save();

        $parser = tthemeparser::i();
        $parser->removephp = isset($removephp);
        $parser->removespaces = isset($removespaces);
        $parser->save();

        $parser = adminparser::i();
        $parser->removephp = isset($removephp);
        $parser->removespaces = isset($removespaces);
        $parser->save();

        $backuper = tbackuper::i();
        if ($backuper->filertype != $filertype) {
            $backuper->filertype = $filertype;
            $backuper->save();
        }

        $useshell = isset($useshell);
        $updater = tupdater::i();
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
        $menus = tadminmenus::i();
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

} //class