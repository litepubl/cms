<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\view\Parser;
use litepubl\view\Filter;
use litepubl\view\AdminParser;
use litepubl\updater\Updater;
use litepubl\updater\Backuper;
use litepubl\admin\Form;
use litepubl\admin\Menus;

class Secure extends \litepubl\admin\Menu
{

    public function getContent() {
        $options =  $this->getApp()->options;
        $lang = Lang::admin('options');
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
        $args->filertype = $this->theme->comboItems(array(
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

    public function processForm() {
        extract($_POST, EXTR_SKIP);
        $options =  $this->getApp()->options;
$admin = $this->admintheme;
$lang = Lang::admin('options');

        if (isset($_POST['oldpassword'])) {
            if ($oldpassword == '') {
                return $admin->geterr($lang->badpassword);
            }

            if (($newpassword == '') || ($newpassword != $repassword)) {
                return $admin->geterr($lang->difpassword);
            }

            if (!$options->auth($options->email, $oldpassword)) {
                return $admin->geterr($lang->badpassword);
            }

            $options->changepassword($newpassword);
            $options->logout();
                return $admin->success($lang->passwordchanged);
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

    public function setUsersenabled($value) {
        if ( $this->getApp()->options->usersenabled == $value) {
            return;
        }

         $this->getApp()->options->usersenabled = $value;
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