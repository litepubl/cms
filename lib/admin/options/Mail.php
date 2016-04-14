<?php

namespace litepubl\admin\options;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\utils\Smtp;
use litepubl\comments\Subscribers;

class Mail extends \litepubl\admin\Menu
{
public function getcontent() {
$options = litepubl::$options;
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
                return $this->admintheme->form('
      [text=adminemail]
      [text=fromemail]
      [text=subscribeemail]
      [checkbox=smtp]
      [text=host]
      [text=smtplogin]
      [password=password]
      [text=port]
      ', $args);
}

public function processform() {
        extract($_POST, EXTR_SKIP);
                if (!empty($adminemail)) {
                    litepubl::$options->email = $adminemail;
                    $this->getdb('users')->setvalue(1, 'email', $adminemail);
                }

                if (!empty($fromemail)) {
litepubl::$options->fromemail = $fromemail;
}

                litepubl::$options->smtp = empty($smtp) ? '' : 'smtp';
                if (!empty($subscribeemail)) {
                    $subscribe = Subscribers::i();
                    $subscribe->fromemail = $subscribeemail;
                    $subscribe->save();
                    litepubl::$options->fromemail = $subscribeemail;
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