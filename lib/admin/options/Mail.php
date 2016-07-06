<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\options;

use litepubl\comments\Subscribers;
use litepubl\utils\Smtp;
use litepubl\view\Args;
use litepubl\view\Lang;

class Mail extends \litepubl\admin\Menu
{
    public function getContent(): string
    {
        $options = $this->getApp()->options;
        $lang = Lang::admin('options');
        $args = new Args();
        $args->adminemail = $options->email;
        $args->fromemail = $options->fromemail;
        $args->mailer = $options->mailer == 'smtp';

        $subscribers = Subscribers::i();
        $args->subscribeemail = $subscribers->fromemail;

        $smtp = Smtp::i();
        $args->host = $smtp->host;
        $args->smtplogin = $smtp->login;
        $args->password = $smtp->password;
        $args->port = $smtp->port;

        $args->formtitle = $lang->mailoptions;
        return $this->admintheme->form(
            '
      [text=adminemail]
      [text=fromemail]
      [text=subscribeemail]
      [checkbox=smtp]
      [text=host]
      [text=smtplogin]
      [password=password]
      [text=port]
      ', $args
        );
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        if (!empty($adminemail)) {
            $this->getApp()->options->email = $adminemail;
            $this->getdb('users')->setvalue(1, 'email', $adminemail);
        }

        if (!empty($fromemail)) {
            $this->getApp()->options->fromemail = $fromemail;
        }

        $this->getApp()->options->smtp = empty($smtp) ? '' : 'smtp';
        if (!empty($subscribeemail)) {
            $subscribe = Subscribers::i();
            $subscribe->fromemail = $subscribeemail;
            $subscribe->save();
            $this->getApp()->options->fromemail = $subscribeemail;
        }

        $smtp = Smtp::i();
        $smtp->lock();
        $smtp->host = $host;
        $smtp->login = $smtplogin;
        $smtp->password = $password;
        $smtp->port = (int)$port;
        $smtp->unlock();
    }
}
